<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = config('insociable.posts_per_page', 20);

        // Curseur de pagination stable :
        // 'before' permet d'aller vers le passé (posts plus anciens)
        // 'after' permet de chercher s'il y a des posts plus récents
        $beforeCreatedAt = $request->query('before_created_at');
        $beforeId = $request->query('before_id');
        $afterCreatedAt = $request->query('after_created_at');
        $afterId = $request->query('after_id');

        $query = Post::query()
            ->with(['user.profile', 'media'])
            ->withCount('comments')
            ->feedFor($user);

        if ($beforeCreatedAt && $beforeId) {
            $parsedBefore = Carbon::parse($beforeCreatedAt)->format('Y-m-d H:i:s');
            $query->where(function ($q) use ($parsedBefore, $beforeId) {
                $q->where('created_at', '<', $parsedBefore)
                  ->orWhere(function ($sub) use ($parsedBefore, $beforeId) {
                      $sub->where('created_at', '=', $parsedBefore)
                          ->where('id', '<', $beforeId);
                  });
            });
        } elseif ($afterCreatedAt && $afterId) {
            $parsedAfter = Carbon::parse($afterCreatedAt)->format('Y-m-d H:i:s');
            $query->where(function ($q) use ($parsedAfter, $afterId) {
                $q->where('created_at', '>', $parsedAfter)
                  ->orWhere(function ($sub) use ($parsedAfter, $afterId) {
                      $sub->where('created_at', '=', $parsedAfter)
                          ->where('id', '>', $afterId);
                  });
            });
        }

        // On prend $perPage + 1 pour déterminer avec certitude s'il reste une page suivante
        $posts = $query->take($perPage + 1)->get();

        $hasMore = $posts->count() > $perPage;
        if ($hasMore) {
            $posts = $posts->slice(0, $perPage);
        }

        $lastPost = $posts->last();
        $firstPost = $posts->first();

        $nextCursor = ($hasMore && $lastPost) ? [
            'before_created_at' => $lastPost->created_at->format('Y-m-d H:i:s'),
            'before_id' => $lastPost->id,
        ] : null;

        $newestCursor = $firstPost ? [
            'after_created_at' => $firstPost->created_at->format('Y-m-d H:i:s'),
            'after_id' => $firstPost->id,
        ] : null;

        // Informations de suspension éventuelle
        $isSuspended = $user ? $user->isWritingSuspended() : false;
        $suspendedUntil = ($user && $user->write_suspended_until)
            ? $user->write_suspended_until->timezone(config('app.timezone', 'UTC'))->format('d/m/Y à H:i')
            : null;

        if ($request->wantsJson()) {
            return response()->json([
                'posts' => $posts,
                'has_more' => $hasMore,
                'next_cursor' => $nextCursor,
                'newest_cursor' => $newestCursor,
            ]);
        }

        return view('feed.index', [
            'posts' => $posts,
            'hasMore' => $hasMore,
            'nextCursor' => $nextCursor,
            'newestCursor' => $newestCursor,
            'isSuspended' => $isSuspended,
            'suspendedUntil' => $suspendedUntil,
            'suspensionReason' => $user?->write_suspended_reason,
        ]);
    }
}
