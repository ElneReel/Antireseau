<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show(string $handle)
    {
        $viewer = Auth::user();
        $lowerHandle = strtolower($handle);

        $profile = Profile::with('user')
            ->where('handle_lower', $lowerHandle)
            ->firstOrFail();

        $targetUser = $profile->user;

        // Vérification de blocage mutuel
        $isBlockedByViewer = $viewer->hasBlocked($targetUser);
        $isBlockingViewer = $targetUser->hasBlocked($viewer);

        if ($isBlockingViewer) {
            abort(404, 'Ce profil est inaccessible.');
        }

        $posts = $targetUser->posts()
            ->with(['media'])
            ->withCount('comments')
            ->published()
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(config('insociable.posts_per_page', 20));

        return view('profiles.show', [
            'profile' => $profile,
            'targetUser' => $targetUser,
            'posts' => $posts,
            'isBlockedByViewer' => $isBlockedByViewer,
            'isSelf' => $viewer->id === $targetUser->id,
        ]);
    }
}
