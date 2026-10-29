<x-app-layout>
    <x-slot:title>Réglages du compte — Insociable</x-slot:title>

    <div style="flex: 1; overflow-y: auto; padding: 20px 16px 80px;">
        <h1 style="font-size: 22px; font-weight: 800; margin-bottom: 20px;">Réglages du compte</h1>

        <!-- Profil -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-glass); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 20px;">
            <h2 style="font-size: 16px; font-weight: 700; margin-bottom: 16px; color: var(--accent);">Mon Profil</h2>

            <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px;">Pseudonyme (@handle)</label>
                    <input type="text" class="creator-input" value="@&lrm;{{ $profile->handle }}" disabled style="opacity: 0.6; cursor: not-allowed;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Nom d'affichage</label>
                    <input type="text" name="display_name" class="creator-input" value="{{ old('display_name', $profile->display_name) }}" maxlength="60">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Bio (240 car. max)</label>
                    <textarea name="bio" class="creator-input" rows="3" maxlength="240">{{ old('bio', $profile->bio) }}</textarea>
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Avatar photo</label>
                    <input type="file" name="avatar" class="creator-input" accept="image/jpeg,image/png,image/webp">
                </div>

                <button type="submit" class="btn-primary-gradient" style="margin-top: 6px;">
                    Enregistrer le profil
                </button>
            </form>
        </div>

        <!-- Mot de passe -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-glass); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 20px;">
            <h2 style="font-size: 16px; font-weight: 700; margin-bottom: 16px; color: var(--accent);">Mot de passe</h2>

            <form method="POST" action="{{ route('settings.password') }}" style="display: flex; flex-direction: column; gap: 12px;">
                @csrf
                @method('PUT')

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Mot de passe actuel</label>
                    <input type="password" name="current_password" required class="creator-input">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Nouveau mot de passe</label>
                    <input type="password" name="password" required class="creator-input" placeholder="8 car. minimum">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Confirmation</label>
                    <input type="password" name="password_confirmation" required class="creator-input">
                </div>

                <button type="submit" class="icon-btn" style="width: 100%; border-radius: var(--radius-full); font-weight: 600; font-size: 13px; height: 42px; border: 1px solid var(--border-subtle); margin-top: 4px;">
                    Changer le mot de passe
                </button>
            </form>
        </div>

        <!-- Comptes bloqués -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-glass); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 20px;">
            <h2 style="font-size: 16px; font-weight: 700; margin-bottom: 10px;">Comptes bloqués ({{ $blockedUsers->count() }})</h2>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                @forelse($blockedUsers as $blocked)
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-subtle);">
                        <span style="font-size: 13px; color: white;">@&lrm;{{ $blocked->profile->handle }}</span>
                        <form method="POST" action="{{ route('blocks.destroy', $blocked->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="color: var(--accent); font-size: 12px; font-weight: 600; background: none;">Débloquer</button>
                        </form>
                    </div>
                @empty
                    <div style="font-size: 13px; color: var(--text-muted);">Aucun compte bloqué.</div>
                @endforelse
            </div>
        </div>

        <!-- Suppression compte -->
        <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: var(--radius-lg); padding: 20px;">
            <h2 style="font-size: 16px; font-weight: 700; color: var(--danger); margin-bottom: 6px;">Suppression définitive</h2>
            <p style="font-size: 12px; color: #fca5a5; line-height: 1.4; margin-bottom: 14px;">
                Cette action est irréversible. Toutes vos publications, images, vidéos et audios seront définitivement effacés.
            </p>

            <form method="POST" action="{{ route('settings.account.destroy') }}" onsubmit="return confirm('Confirmer la suppression irréversible ?');">
                @csrf
                @method('DELETE')
                <div style="display: flex; gap: 8px;">
                    <input type="password" name="password" required placeholder="Votre mot de passe..." class="creator-input" style="font-size: 13px;">
                    <button type="submit" style="background: var(--danger); color: white; border-radius: var(--radius-full); font-size: 13px; font-weight: 700; padding: 0 16px; white-space: nowrap;">
                        Supprimer
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
