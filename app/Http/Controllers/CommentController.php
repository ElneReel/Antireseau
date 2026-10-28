<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Services\AntispamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function __construct(
        protected AntispamService $antispamService
    ) {}

    public function store(StoreCommentRequest $request, int $postId)
    {
        $user = Auth::user();
        $post = Post::findOrFail($postId);

        Gate::authorize('comment', $post);

        $idempotencyKey = $request->header('X-Idempotency-Key') ?? $request->input('idempotency_key');
        if ($idempotencyKey) {
            $cached = $this->antispamService->checkIdempotency($user, 'comment', $idempotencyKey);
            if ($cached) {
                return redirect()->route('posts.show', $post->id)->with('info', 'Commentaire déjà enregistré.');
            }
        }

        $validation = $this->antispamService->validateCommentSubmission(
            $user,
            $request->input('body'),
            $request->ip()
        );

        if (!$validation['allowed']) {
            return redirect()->route('posts.show', $post->id)
                ->withInput()
                ->with('error', $validation['reason']);
        }

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => $request->input('body'),
            'status' => 'published',
        ]);

        $this->antispamService->recordRateLimitEvent(
            $user,
            'comment',
            'accepted',
            $validation['payload_hash'],
            $request->ip()
        );

        if ($idempotencyKey) {
            $this->antispamService->storeIdempotencyResult(
                $user,
                'comment',
                $idempotencyKey,
                302,
                ['Location' => route('posts.show', $post->id)],
                json_encode(['comment_id' => $comment->id])
            );
        }

        return redirect()->route('posts.show', $post->id)->with('success', 'Commentaire publié.');
    }

    public function destroy(int $id)
    {
        $comment = Comment::with('post')->findOrFail($id);
        Gate::authorize('delete', $comment);

        $postId = $comment->post_id;
        $comment->delete();

        return redirect()->route('posts.show', $postId)->with('info', 'Commentaire retiré.');
    }
}
