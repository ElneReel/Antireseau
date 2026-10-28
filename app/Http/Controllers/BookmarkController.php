<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Publications sauvegardées qui sont toujours actives et dont l'auteur n'est pas en conflit de blocage
        $conflictIds = $user->getBlockedUserIds();

        $bookmarks = Bookmark::where('user_id', $user->id)
            ->whereHas('post', function ($query) use ($conflictIds) {
                $query->published();
                if (!empty($conflictIds)) {
                    $query->whereNotIn('user_id', $conflictIds);
                }
            })
            ->with(['post.user.profile', 'post.media'])
            ->orderBy('created_at', 'desc')
            ->paginate(config('insociable.posts_per_page', 20));

        return view('bookmarks.index', [
            'bookmarks' => $bookmarks,
        ]);
    }

    public function toggle(int $postId)
    {
        $user = Auth::user();
        $post = Post::published()->findOrFail($postId);

        // Si bloqué, refus
        if ($user->hasBlocked($post->user_id) || $user->isBlockedBy($post->user_id)) {
            abort(403);
        }

        $existing = Bookmark::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $bookmarked = false;
            $message = 'Publication retirée de tes sauvegardes.';
        } else {
            Bookmark::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
            $bookmarked = true;
            $message = 'Publication gardée dans tes sauvegardes.';
        }

        if (request()->wantsJson()) {
            return response()->json([
                'bookmarked' => $bookmarked,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('info', $message);
    }
}
