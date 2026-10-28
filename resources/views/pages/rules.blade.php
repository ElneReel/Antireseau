<x-app-layout>
    <x-slot:title>Règles du lieu - Insociable</x-slot:title>

    <div class="reading-container" style="padding-top: 32px; padding-bottom: 60px;">
        <h1 class="section-title">Règles du lieu</h1>
        <p style="color: var(--muted); font-size: 15px; margin-bottom: 32px;">
            Document provisoire de prototype. Ces règles fixent le cadre de vie commune sur Insociable.
        </p>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 22px;">1. Pas d’optimisation artificielle</h2>
            <p>
                Insociable ne récompense pas le volume. Deux publications consécutives doivent être espacées d'au moins 60 secondes. Chaque compte dispose d'un quota de 5 publications acceptées par 24 heures glissantes.
            </p>
        </section>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 22px;">2. Interdiction absolue du spam</h2>
            <p>
                Soumettre le même texte trois fois en dix minutes ou enchaîner dix tentatives refusées consécutives déclenche automatiquement une suspension de publication et de commentaire de 24 heures.
            </p>
        </section>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 22px;">3. Respect des membres et autonomie</h2>
            <p>
                Tu peux bloquer n'importe quel compte à tout moment. Le blocage est bilatéral : vous ne verrez plus les contenus de l'autre et ne pourrez plus interagir. Si un contenu contrevient à la loi ou harcèle, utilise le bouton de signalement avec motif.
            </p>
        </section>

        <section style="margin-bottom: 32px;">
            <h2 style="font-size: 22px;">4. Droit à l'effacement immédiat</h2>
            <p>
                Tu es propriétaire de tes mots. La suppression d'une publication retire immédiatement le texte et détruit le fichier image associé. La suppression de ton compte purge définitivement toutes tes données et révoque tes sessions.
            </p>
        </section>

        <div style="margin-top: 40px; border-top: 1px solid var(--border); padding-top: 20px;">
            <a href="{{ route('home') }}">← Retour à l’accueil</a>
        </div>
    </div>
</x-app-layout>
