<x-app-layout>
    <x-slot:title>Contester une suspension - Insociable</x-slot:title>

    <div class="reading-container" style="max-width: 600px; padding-top: 32px; padding-bottom: 60px;">
        <nav style="margin-bottom: 24px;">
            <a href="{{ route('feed') }}" class="no-underline" style="font-size: 14px; color: var(--muted);">← Retour au fil</a>
        </nav>

        <h1 class="section-title">Contester une suspension d'écriture</h1>

        <div class="alert alert-suspension" style="margin-bottom: 28px;">
            <div>
                <strong>Statut actuel :</strong> Ton compte est actuellement suspendu d'écriture jusqu'au
                <strong>{{ $user->write_suspended_until->timezone(config('app.timezone', 'UTC'))->format('d/m/Y à H:i') }}</strong>.
                @if($user->write_suspended_reason)
                    <div style="font-size: 13px; margin-top: 6px;">Motif : {{ $user->write_suspended_reason }}</div>
                @endif
            </div>
        </div>

        @if($existingPending)
            <div class="surface-card">
                <h2 style="font-size: 18px; margin-bottom: 12px;">Contestation déjà en cours</h2>
                <p style="font-size: 14px; color: var(--muted);">
                    Tu as déjà soumis une contestation le {{ $existingPending->created_at->format('d/m/Y à H:i') }}.
                    Elle est actuellement en attente d'examen par l'équipe de modération.
                </p>
                <div style="background-color: var(--paper); padding: 12px; border-radius: var(--radius); font-style: italic; font-size: 14px;">
                    « {{ $existingPending->message }} »
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('appeals.store') }}" class="surface-card">
                @csrf

                <div class="form-group">
                    <label for="appeal-message" class="form-label">Explique la situation :</label>
                    <textarea id="appeal-message"
                              name="message"
                              class="form-textarea"
                              rows="5"
                              placeholder="Si tu penses qu'il s'agit d'une erreur ou d'un incident réseau..."
                              maxlength="1500"
                              required>{{ old('message') }}</textarea>
                    <div class="form-help">Une seule contestation peut être soumise par période de suspension.</div>
                    @error('message')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary">
                        Envoyer ma contestation
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-app-layout>
