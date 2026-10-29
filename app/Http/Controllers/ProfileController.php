<?php

namespace App\Http\Controllers;

use App\Models\Profile;
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

        // 1. Ses créations (Publications)
        $posts = $targetUser->posts()
            ->with(['media', 'user.profile'])
            ->withCount(['comments', 'likes', 'reposts'])
            ->published()
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // 2. Ses repartages (Ce qu'il a fait remonter dans le fil)
        $repostedPosts = $targetUser->repostedPosts()
            ->with(['media', 'user.profile'])
            ->withCount(['comments', 'likes', 'reposts'])
            ->where('status', 'published')
            ->orderByPivot('created_at', 'desc')
            ->get();

        // 3. Ses likes (Ce qu'il a aimé)
        $likedPosts = $targetUser->likedPosts()
            ->with(['media', 'user.profile'])
            ->withCount(['comments', 'likes', 'reposts'])
            ->where('status', 'published')
            ->orderByPivot('created_at', 'desc')
            ->get();

        return view('profiles.show', [
            'profile' => $profile,
            'targetUser' => $targetUser,
            'posts' => $posts,
            'repostedPosts' => $repostedPosts,
            'likedPosts' => $likedPosts,
            'isBlockedByViewer' => $isBlockedByViewer,
            'isSelf' => $viewer->id === $targetUser->id,
        ]);
    }
}
