<x-app-layout>
    <x-slot:title>Rejoindre Insociable - Inscription</x-slot:title>

    <div class="reading-container" style="max-width: 520px; padding-top: 40px; padding-bottom: 60px;">
        <h1 class="section-title">Rejoindre Insociable</h1>
        <p style="color: var(--muted); margin-bottom: 28px;">
            Une inscription simple. Aucun ami à importer, personne à suivre.
        </p>

        <form method="POST" action="{{ route('register') }}" class="surface-card">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Adresse email (reste strictement privée)</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-input" required autocomplete="email" autofocus>
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="handle" class="form-label">Pseudonyme unique (handle)</label>
                <div style="display: flex; align-items: center; gap: 4px;">
                    <span style="color: var(--muted); font-size: 16px;">@</span>
                    <input type="text" id="handle" name="handle" value="{{ old('handle') }}" class="form-input" required placeholder="mon_pseudonyme" maxlength="24">
                </div>
                <div class="form-help">3 à 24 caractères : lettres sans accent, chiffres et tiret bas (_).</div>
                @error('handle')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="display_name" class="form-label">Nom d'affichage (facultatif)</label>
                <input type="text" id="display_name" name="display_name" value="{{ old('display_name') }}" class="form-input" placeholder="Ex: Alex V." maxlength="60">
                <div class="form-help">Visible par les autres membres. Caractères accentués acceptés.</div>
                @error('display_name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" required autocomplete="new-password">
                <div class="form-help">8 caractères minimum.</div>
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required autocomplete="new-password">
            </div>

            <div class="form-group" style="margin-top: 24px;">
                <label class="form-check">
                    <input type="checkbox" name="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} required>
                    <span style="font-size: 14px;">
                        J'ai lu et j'accepte les <a href="{{ route('rules') }}" target="_blank">règles du lieu</a> (respect, absence de spam et modération).
                    </span>
                </label>
                @error('terms_accepted')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="is_adult" value="1" {{ old('is_adult') ? 'checked' : '' }} required>
                    <span style="font-size: 14px;">
                        Je confirme être majeur (au moins 18 ans) pour participer à cette version.
                    </span>
                </label>
                @error('is_adult')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-top: 28px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Créer mon compte
                </button>
            </div>

            <div style="margin-top: 20px; text-align: center; font-size: 14px; color: var(--muted);">
                Déjà inscrit ? <a href="{{ route('login') }}">Se connecter</a>
            </div>
        </form>
    </div>
</x-app-layout>
