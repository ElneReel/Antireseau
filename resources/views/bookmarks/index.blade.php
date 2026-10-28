<x-app-layout>
    <x-slot:title>Mes Sauvegardes - Insociable</x-slot:title>

    <div class="reading-container" style="padding-top: 24px; padding-bottom: 60px;">
        <div style="border-bottom: 2px solid var(--ink); padding-bottom: 12px; margin-bottom: 24px;">
            <h1 style="font-size: 24px; font-weight: 600; margin: 0;">
                Publications sauvegardées
            </h1>
            <p style="font-size: 14px; color: var(--muted); margin-top: 4px; margin-bottom: 0;">
                Ta liste privée de lecture. Les auteurs ne sont pas notifiés de tes sauvegardes.
            </p>
        </div>

        @forelse($bookmarks as $bookmark)
            <x-post-item :post="$bookmark->post" />
        @empty
            <div style="padding: 60px 0; text-align: center; color: var(--muted);">
                <p style="font-family: var(--font-serif); font-size: 20px;">Aucune publication sauvegardée.</p>
                <p style="font-size: 15px;">Clique sur « Sauvegarder » sous n'importe quelle publication du fil pour la retrouver ici.</p>
            </div>
        @endforelse

        <div style="margin-top: 24px;">
            {{ $bookmarks->links() }}
        </div>
    </div>
</x-app-layout>
