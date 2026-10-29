<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function show(int $id)
    {
        $viewer = Auth::user();

        // Le média n'est accessible qu'aux membres connectés
        if (!$viewer) {
            abort(403, 'Accès réservé aux membres connectés.');
        }

        $media = Media::with('post')->findOrFail($id);

        // Si le média est rattaché à une publication
        if ($media->post) {
            // Si le post n'est pas publié et que le viewer n'est pas l'auteur ni admin
            if ($media->post->status !== 'published' && $viewer->id !== $media->post->user_id && !$viewer->isAdmin()) {
                abort(404);
            }

            // Vérification de blocage mutuel
            if ($viewer->hasBlocked($media->post->user_id) || $viewer->isBlockedBy($media->post->user_id)) {
                abort(403, 'Contenu inaccessible.');
            }
        }

        if (!Storage::disk($media->disk)->exists($media->file_path)) {
            abort(404, 'Fichier introuvable.');
        }

        $headers = [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'inline; filename="' . addslashes($media->original_name) . '"',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
        ];

        return Storage::disk($media->disk)->response($media->file_path, null, $headers);
    }

    public function avatar(int $userId)
    {
        $viewer = Auth::user();
        if (!$viewer) {
            abort(403);
        }

        $user = User::with('profile')->findOrFail($userId);

        if (!$user->profile || !$user->profile->avatar_path) {
            abort(404);
        }

        // Vérification de blocage
        if ($viewer->hasBlocked($user) || $user->hasBlocked($viewer)) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($user->profile->avatar_path)) {
            abort(404);
        }

        $headers = [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ];

        return Storage::disk('local')->response($user->profile->avatar_path, null, $headers);
    }
}
