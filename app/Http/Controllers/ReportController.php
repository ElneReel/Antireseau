<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\ReportEvidence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        $targetType = $validated['target_type'];
        $targetId = (int) $validated['target_id'];

        // Construire le snapshot pour la preuve
        $snapshot = match ($targetType) {
            'post' => (function () use ($targetId) {
                $post = Post::with(['user.profile', 'media'])->find($targetId);
                return $post ? [
                    'type' => 'post',
                    'id' => $post->id,
                    'author_id' => $post->user_id,
                    'author_handle' => $post->user->profile->handle ?? null,
                    'body' => $post->body,
                    'created_at' => $post->created_at->toISOString(),
                    'media_original_name' => $post->media?->original_name,
                    'media_hash' => $post->media?->file_hash,
                ] : null;
            })(),
            'comment' => (function () use ($targetId) {
                $comment = Comment::with(['user.profile', 'post'])->find($targetId);
                return $comment ? [
                    'type' => 'comment',
                    'id' => $comment->id,
                    'post_id' => $comment->post_id,
                    'author_id' => $comment->user_id,
                    'author_handle' => $comment->user->profile->handle ?? null,
                    'body' => $comment->body,
                    'created_at' => $comment->created_at->toISOString(),
                ] : null;
            })(),
            'user' => (function () use ($targetId) {
                $u = User::with('profile')->find($targetId);
                return $u ? [
                    'type' => 'user',
                    'id' => $u->id,
                    'handle' => $u->profile->handle ?? null,
                    'display_name' => $u->profile->display_name ?? null,
                    'bio' => $u->profile->bio ?? null,
                ] : null;
            })(),
            default => null,
        };

        if (!$snapshot) {
            return redirect()->back()->with('error', "Le contenu ciblé n'existe plus ou est inaccessible.");
        }

        DB::transaction(function () use ($user, $validated, $snapshot) {
            $report = Report::create([
                'reporter_id' => $user->id,
                'target_type' => $validated['target_type'],
                'target_id' => $validated['target_id'],
                'reason' => $validated['reason'],
                'details' => $validated['details'] ?? null,
                'status' => 'open',
            ]);

            ReportEvidence::create([
                'report_id' => $report->id,
                'snapshot' => $snapshot,
            ]);
        });

        return redirect()->back()->with('info', 'Signalement transmis à l’équipe de modération. Merci de ta vigilance.');
    }
}
