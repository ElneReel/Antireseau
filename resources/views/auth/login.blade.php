<x-app-layout>
    <x-slot:title>Connexion — antireseau</x-slot:title>

    <div style="flex: 1; overflow-y: auto; padding: 40px 20px; display: flex; flex-direction: column; justify-content: center;">
        <div style="max-width: 380px; width: 100%; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 32px;">
                <img src="{{ asset('images/logo-ar.svg') }}" alt="antireseau" width="60" height="60" style="margin-bottom: 16px; filter: drop-shadow(0 0 16px rgba(244, 63, 94, 0.4));">
                <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 8px;">Bon retour</h1>
                <p style="color: var(--text-muted); font-size: 14px;">Accédez à <strong style="color: white;">antireseau</strong> en temps réel</p>
            </div>

            <form method="POST" action="{{ route('login') }}" style="display: flex; flex-direction: column; gap: 16px;">
                @csrf

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Adresse email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="creator-input" placeholder="nom@exemple.com">
                    @error('email')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Mot de passe</label>
                    <input type="password" name="password" required class="creator-input" placeholder="••••••••">
                    @error('password')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="remember" value="1" checked style="accent-color: var(--accent);">
                        <span>Rester connecté</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary-gradient" style="margin-top: 12px;">
                    Se connecter
                </button>
            </form>

            <div style="text-align: center; margin-top: 28px; font-size: 14px; color: var(--text-muted);">
                Pas encore de compte ? <a href="{{ route('register') }}" style="color: var(--accent); font-weight: 700;">Rejoindre</a>
            </div>
        </div>
    </div>
</x-app-layout>
