<x-app-layout>
    <x-slot:title>Rejoindre — antireseau</x-slot:title>

    <div style="flex: 1; overflow-y: auto; padding: 32px 20px 50px;">
        <div style="max-width: 380px; width: 100%; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 24px;">
                <img src="{{ asset('images/logo-ar.svg') }}" alt="antireseau" width="58" height="58" style="margin-bottom: 14px; filter: drop-shadow(0 0 16px rgba(244, 63, 94, 0.4));">
                <h1 style="font-size: 26px; font-weight: 800; margin-bottom: 6px;">Créer un profil</h1>
                <p style="color: var(--text-muted); font-size: 13px;">Rejoignez <strong style="color: white;">antireseau</strong> &bull; 0 abonnement &bull; 0 algo</p>
            </div>

            <form method="POST" action="{{ route('register') }}" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Email (privé)</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="creator-input" placeholder="nom@exemple.com">
                    @error('email')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Pseudonyme (@handle)</label>
                    <input type="text" name="handle" value="{{ old('handle') }}" required class="creator-input" placeholder="mon_pseudo" maxlength="24">
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Lettres a-z, chiffres et tiret bas (_)</div>
                    @error('handle')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Nom d'affichage (optionnel)</label>
                    <input type="text" name="display_name" value="{{ old('display_name') }}" class="creator-input" placeholder="Ex: Alex V." maxlength="60">
                    @error('display_name')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Mot de passe</label>
                    <input type="password" name="password" required class="creator-input" placeholder="8 caractères minimum">
                    @error('password')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Confirmation</label>
                    <input type="password" name="password_confirmation" required class="creator-input" placeholder="Répéter le mot de passe">
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 6px;">
                    <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 12px; color: var(--text-secondary); cursor: pointer;">
                        <input type="checkbox" name="terms_accepted" value="1" required style="accent-color: var(--accent); margin-top: 2px;">
                        <span>J'accepte les règles du lieu (absence de spam, respect mutuel)</span>
                    </label>
                    <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 12px; color: var(--text-secondary); cursor: pointer;">
                        <input type="checkbox" name="is_adult" value="1" required style="accent-color: var(--accent); margin-top: 2px;">
                        <span>Je confirme avoir au moins 18 ans</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary-gradient" style="margin-top: 10px;">
                    Créer mon profil
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px; font-size: 13px; color: var(--text-muted);">
                Déjà membre ? <a href="{{ route('login') }}" style="color: var(--accent); font-weight: 700;">Se connecter</a>
            </div>
        </div>
    </div>
</x-app-layout>
