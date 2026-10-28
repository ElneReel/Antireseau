<x-app-layout>
    <x-slot:title>Confidentialité & Données - Insociable</x-slot:title>

    <div class="reading-container" style="padding-top: 32px; padding-bottom: 60px;">
        <h1 class="section-title">Confidentialité & Vie privée</h1>
        <p style="color: var(--muted); font-size: 15px; margin-bottom: 32px;">
            Texte provisoire pour le prototype local.
        </p>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 22px;">Ce que nous ne faisons pas</h2>
            <p>
                Aucun pisteur tiers. Aucun profilage publicitaire. Aucun tri algorithmique comportemental. Nous n'analysons pas tes habitudes de lecture.
            </p>
        </section>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 22px;">Données collectées</h2>
            <p>
                Ton adresse email (qui reste strictement privée), ton pseudonyme, ton mot de passe chiffré, et les publications ou commentaires que tu choisis de rendre publics dans le fil.
            </p>
        </section>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 22px;">Traitement des images</h2>
            <p>
                Toute image téléchargée est réencodée pour supprimer toutes les métadonnées (dont la localisation GPS et le type d'appareil). Les images sont stockées dans un espace privé et servies uniquement aux membres connectés autorisés.
            </p>
        </section>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 22px;">Rétention et antispam</h2>
            <p>
                Les journaux d'événements techniques de limitation (rate limit) conservent uniquement des condensats numériques (empreintes cryptographiques) et sont purgés automatiquement après une durée bornée de 14 jours.
            </p>
        </section>

        <div style="margin-top: 40px; border-top: 1px solid var(--border); padding-top: 20px;">
            <a href="{{ route('home') }}">← Retour à l’accueil</a>
        </div>
    </div>
</x-app-layout>
