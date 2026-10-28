<x-app-layout>
    <x-slot:title>Connexion - Insociable</x-slot:title>

    <div class="reading-container" style="max-width: 480px; padding-top: 40px; padding-bottom: 60px;">
        <h1 class="section-title">Connexion</h1>
        <p style="color: var(--muted); margin-bottom: 28px;">
            Accède au fil chronologique commun.
        </p>

        <form method="POST" action="{{ route('login') }}" class="surface-card">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Adresse email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-input" required autocomplete="email" autofocus>
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-input" required autocomplete="current-password">
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-top: 18px;">
                <label class="form-check">
                    <input type="checkbox" name="remember" value="1">
                    <span style="font-size: 14px;">Garder ma session active sur cet appareil</span>
                </label>
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Entrer sur le fil
                </button>
            </div>

            <div style="margin-top: 20px; text-align: center; font-size: 14px; color: var(--muted);">
                Pas encore de compte ? <a href="{{ route('register') }}">Rejoindre le lieu</a>
            </div>
        </form>
    </div>
</x-app-layout>
