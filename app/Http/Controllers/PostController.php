<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostRepost;
use App\Services\AntispamService;
use App\Services\MediaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function __construct(
        protected AntispamService $antispamService,
        protected MediaService $mediaService
    ) {}

    public function store(StorePostRequest $request)
    {
        $user = Auth::user();
        $idempotencyKey = $request->header('X-Idempotency-Key') ?? $request->input('idempotency_key');

        // 1. Vérification de l'idempotence
        if ($idempotencyKey) {
            $cached = $this->antispamService->checkIdempotency($user, 'post', $idempotencyKey);
            if ($cached) {
                if ($request->wantsJson()) {
                    return response($cached->response_body, $cached->status_code, $cached->response_headers ?? []);
                }
                return redirect()->route('feed')->with('info', 'Publication déjà enregistrée.');
            }
        }

        // 2. Calcul du condensat de fichier si présent
        $fileHash = null;
        $mediaUpload = $request->file('media_file') ?? $request->file('image');
        if ($mediaUpload && $mediaUpload->isValid()) {
            $fileHash = hash_file('sha256', $mediaUpload->getRealPath());
        }

        // 3. Validation Antispam atomique
        $validation = $this->antispamService->validatePostSubmission(
            $user,
            $request->input('body'),
            $fileHash,
            $request->ip()
        );

        if (!$validation['allowed']) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => $validation['reason'],
                    'suspended' => $validation['suspended'],
                    'retry_after' => $validation['retry_after_seconds'],
                ], 429);
            }

            return redirect()->back()->withInput()->with('error', $validation['reason']);
        }

        // 4. Création transactionnelle du post et du média
        $post = DB::transaction(function () use ($request, $user, $validation, $mediaUpload) {
            $post = Post::create([
                'user_id' => $user->id,
                'body' => $request->input('body') ?? '',
                'comments_allowed' => $request->boolean('comments_allowed', true),
                'status' => 'published',
                'content_hash' => $validation['payload_hash'],
            ]);

            if ($mediaUpload && $mediaUpload->isValid()) {
                $this->mediaService->processPostMedia(
                    $mediaUpload,
                    $user,
                    $request->input('alt_text')
                )->update(['post_id' => $post->id]);
            }

            // Enregistrement de l'événement accepté
            $this->antispamService->recordRateLimitEvent(
                $user,
                'post',
                'accepted',
                $validation['payload_hash'],
                $request->ip()
            );

            return $post;
        });

        // 5. Stockage du résultat d'idempotence
        if ($idempotencyKey) {
            $this->antispamService->storeIdempotencyResult(
                $user,
                'post',
                $idempotencyKey,
                302,
                ['Location' => route('feed')],
                json_encode(['post_id' => $post->id])
            );
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'post_id' => $post->id], 201);
        }

        return redirect()->route('feed')->with('success', 'Publication partagée.');
    }

    public function show(int $id)
    {
        $post = Post::with(['user.profile', 'lastRepostedBy.profile', 'media', 'comments.user.profile'])
            ->withCount(['comments', 'likes', 'reposts'])
            ->findOrFail($id);

        Gate::authorize('view', $post);

        $isBookmarked = $post->isBookmarkedBy(Auth::user());

        return view('posts.show', [
            'post' => $post,
            'isBookmarked' => $isBookmarked,
        ]);
    }

    public function edit(int $id)
    {
        $post = Post::findOrFail($id);
        Gate::authorize('update', $post);

        return view('posts.edit', ['post' => $post]);
    }

    public function update(UpdatePostRequest $request, int $id)
    {
        $post = Post::findOrFail($id);
        Gate::authorize('update', $post);

        // Mise à jour du texte et de edited_at SANS toucher created_at
        $post->update([
            'body' => $request->input('body'),
            'comments_allowed' => $request->boolean('comments_allowed', true),
            'edited_at' => Carbon::now('UTC'),
        ]);

        return redirect()->route('posts.show', $post->id)->with('success', 'Publication modifiée.');
    }

    public function destroy(int $id)
    {
        $post = Post::with('media')->findOrFail($id);
        Gate::authorize('delete', $post);

        DB::transaction(function () use ($post) {
            if ($post->media) {
                $this->mediaService->deleteMedia($post->media);
            }
            $post->delete();
        });

        return redirect()->route('feed')->with('info', 'Publication retirée.');
    }

    public function toggleComments(int $id)
    {
        $post = Post::findOrFail($id);
        Gate::authorize('toggleComments', $post);

        $post->update([
            'comments_allowed' => !$post->comments_allowed,
        ]);

        $message = $post->comments_allowed
            ? 'Les commentaires sont maintenant ouverts pour cette publication.'
            : 'Les commentaires sont maintenant fermés pour cette publication.';

        return redirect()->back()->with('info', $message);
    }

    /**
     * Bascule le Like sur une publication.
     */
    public function toggleLike(Request $request, int $id)
    {
        $user = Auth::user();
        $post = Post::published()->findOrFail($id);

        $existing = PostLike::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            PostLike::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
            $liked = true;
        }

        $likesCount = PostLike::where('post_id', $post->id)->count();

        if ($request->wantsJson()) {
            return response()->json([
                'liked' => $liked,
                'likes_count' => $likesCount,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Bascule le Repartage (Écho / Propulsion) qui fait remonter la publication en haut du fil !
     */
    public function toggleRepost(Request $request, int $id)
    {
        $user = Auth::user();
        $post = Post::published()->findOrFail($id);

        $existing = PostRepost::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $reposted = false;

            // Recalcul du dernier repartageur pour bumped_at
            $latestRepost = PostRepost::where('post_id', $post->id)
                ->latest('created_at')
                ->first();

            $post->update([
                'bumped_at' => $latestRepost ? $latestRepost->created_at : $post->created_at,
                'last_reposted_by_id' => $latestRepost ? $latestRepost->user_id : null,
            ]);
        } else {
            PostRepost::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
            $reposted = true;

            // Fait REMONTER la publication tout en haut du fil !
            $post->update([
                'bumped_at' => Carbon::now(),
                'last_reposted_by_id' => $user->id,
            ]);
        }

        $repostsCount = PostRepost::where('post_id', $post->id)->count();

        if ($request->wantsJson()) {
            return response()->json([
                'reposted' => $reposted,
                'reposts_count' => $repostsCount,
                'reposter_handle' => $reposted ? $user->profile->handle : null,
                'reposter_name' => $reposted ? $user->profile->effective_name : null,
            ]);
        }

        return redirect()->back()->with('success', $reposted ? 'Publication remontée en tête du fil !' : 'Repartage annulé.');
    }
}
