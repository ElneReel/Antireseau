<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    public function edit()
    {
        $user = Auth::user();
        $profile = $user->profile;
        $blockedUsers = $user->blockedUsers()->with('profile')->get();

        return view('settings.edit', [
            'user' => $user,
            'profile' => $profile,
            'blockedUsers' => $blockedUsers,
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $profile = $user->profile;
        $validated = $request->validated();

        $data = [
            'display_name' => $validated['display_name'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ];

        if ($request->boolean('remove_avatar') && $profile->avatar_path) {
            if (Storage::disk('local')->exists($profile->avatar_path)) {
                Storage::disk('local')->delete($profile->avatar_path);
            }
            $data['avatar_path'] = null;
        } elseif ($request->hasFile('avatar')) {
            if ($profile->avatar_path && Storage::disk('local')->exists($profile->avatar_path)) {
                Storage::disk('local')->delete($profile->avatar_path);
            }
            $data['avatar_path'] = $this->mediaService->processAvatar($request->file('avatar'), $user);
        }

        $profile->update($data);

        return redirect()->route('settings.edit')->with('success', 'Profil mis à jour.');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('settings.edit')->with('success', 'Mot de passe modifié avec succès.');
    }

    public function destroyAccount(DeleteAccountRequest $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        DB::transaction(function () use ($user, $userId) {
            // 1. Purge physique des fichiers des publications
            $medias = Media::where('user_id', $userId)->get();
            foreach ($medias as $media) {
                $this->mediaService->deleteMedia($media);
            }

            // 2. Purge de l'avatar éventuel
            if ($user->profile && $user->profile->avatar_path) {
                if (Storage::disk('local')->exists($user->profile->avatar_path)) {
                    Storage::disk('local')->delete($user->profile->avatar_path);
                }
            }

            // 3. Suppression en base (cascade delete sur posts, comments, bookmarks, etc.)
            User::where('id', $userId)->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Ton compte et toutes tes données associées ont été définitivement supprimés.');
    }
}
