<x-app-layout>
    <x-slot:title>Réglages du compte - Insociable</x-slot:title>

    <div class="reading-container" style="max-width: 640px; padding-top: 32px; padding-bottom: 80px;">
        <h1 class="section-title">Réglages du compte</h1>

        <!-- Section 1 : Profil -->
        <section class="surface-card" style="margin-bottom: 32px;">
            <h2 style="font-size: 20px; margin-bottom: 20px;">Mon Profil</h2>

            <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Pseudonyme unique</label>
                    <input type="text" class="form-input" value="@&lrm;{{ $profile->handle }}" disabled style="background-color: rgba(0,0,0,0.04); cursor: not-allowed;">
                    <div class="form-help">Le pseudonyme est fixe pour garantir la cohérence des échanges.</div>
                </div>

                <div class="form-group">
                    <label for="display_name" class="form-label">Nom d'affichage</label>
                    <input type="text" id="display_name" name="display_name" class="form-input" value="{{ old('display_name', $profile->display_name) }}" maxlength="60">
                    @error('display_name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="bio" class="form-label">Biographie (240 caractères max)</label>
                    <textarea id="bio" name="bio" class="form-textarea" rows="3" maxlength="{{ config('insociable.bio_max_length', 240) }}">{{ old('bio', $profile->bio) }}</textarea>
                    <div class="form-help">Une phrase courte sur ce qui t'intéresse.</div>
                    @error('bio')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Avatar (optionnel)</label>
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 10px;">
                        @if($profile->avatar_path)
                            <img src="{{ route('media.avatar', $user->id) }}" alt="Avatar actuel" class="author-avatar" style="width: 50px; height: 50px;">
                            <label class="form-check" style="min-height: auto;">
                                <input type="checkbox" name="remove_avatar" value="1" style="width: 16px; height: 16px;">
                                <span style="font-size: 13px; color: var(--danger);">Supprimer l'avatar actuel</span>
                            </label>
                        @endif
                    </div>
                    <input type="file" name="avatar" class="form-input" accept="image/jpeg,image/png,image/webp">
                    <div class="form-help">JPEG, PNG ou WebP, max 3 Mo. L'image sera réencodée et les métadonnées supprimées.</div>
                    @error('avatar')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary">
                        Mettre à jour le profil
                    </button>
                </div>
            </form>
        </section>

        <!-- Section 2 : Mot de passe -->
        <section class="surface-card" style="margin-bottom: 32px;">
            <h2 style="font-size: 20px; margin-bottom: 20px;">Sécurité & Mot de passe</h2>

            <form method="POST" action="{{ route('settings.password') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="current_password" class="form-label">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" class="form-input" required autocomplete="current-password">
                    @error('current_password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="new_password" class="form-label">Nouveau mot de passe (8 caractères minimum)</label>
                    <input type="password" id="new_password" name="password" class="form-input" required autocomplete="new-password">
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required autocomplete="new-password">
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn btn-secondary">
                        Changer de mot de passe
                    </button>
                </div>
            </form>
        </section>

        <!-- Section 3 : Comptes bloqués -->
        <section class="surface-card" style="margin-bottom: 32px;">
            <h2 style="font-size: 20px; margin-bottom: 12px;">Comptes bloqués ({{ $blockedUsers->count() }})</h2>
            <p style="font-size: 14px; color: var(--muted); margin-bottom: 20px;">
                Les comptes ci-dessous n'apparaissent plus dans ton fil et ne peuvent pas interagir avec toi.
            </p>

            @forelse($blockedUsers as $blocked)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border);">
                    <div>
                        <strong>{{ $blocked->profile->effective_name }}</strong>
                        <span style="color: var(--muted); font-size: 14px;">@&lrm;{{ $blocked->profile->handle }}</span>
                    </div>
                    <form method="POST" action="{{ route('blocks.destroy', $blocked->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-secondary btn-sm">
                            Débloquer
                        </button>
                    </form>
                </div>
            @empty
                <div style="color: var(--muted); font-size: 14px;">
                    Tu n'as bloqué aucun compte pour le moment.
                </div>
            @endforelse
        </section>

        <!-- Section 4 : Suppression de compte -->
        <section class="surface-card" style="border-color: #f5c6cb; background-color: #fcf8f8;">
            <h2 style="font-size: 20px; margin-bottom: 12px; color: var(--danger);">Suppression définitive du compte</h2>
            <p style="font-size: 14px; color: var(--ink); margin-bottom: 20px;">
                Attention : cette action est immédiate et irréversible. Toutes tes publications, commentaires, sauvegardes et fichiers médias physiques seront définitivement effacés. Toutes tes sessions actives seront révoquées.
            </p>

            <form method="POST" action="{{ route('settings.account.destroy') }}" onsubmit="return confirm('Confirmer la suppression irréversible de ton compte ? Toutes tes données seront immédiatement effacées.');">
                @csrf
                @method('DELETE')

                <div class="form-group">
                    <label for="delete_password" class="form-label">Saisis ton mot de passe actuel pour confirmer :</label>
                    <input type="password" id="delete_password" name="password" class="form-input" style="max-width: 320px;" required autocomplete="current-password">
                    @error('password', 'deleteAccount')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-danger">
                        Supprimer définitivement mon compte et mes données
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
