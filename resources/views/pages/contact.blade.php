<x-app-layout>
    <x-slot:title>Contact - Insociable</x-slot:title>

    <div class="reading-container" style="padding-top: 32px; padding-bottom: 60px;">
        <h1 class="section-title">Contact & Principes</h1>
        <p style="color: var(--muted); font-size: 15px; margin-bottom: 32px;">
            Insociable est une initiative indépendante, conçue sans modèle économique fondé sur l'attention ou la revente de données.
        </p>

        <section class="surface-card" style="margin-bottom: 32px;">
            <p>
                Pour toute question technique, signalement d'urgence ou retour sur le prototype local, tu peux contacter l’équipe de développement :
            </p>
            <p style="font-family: monospace; font-size: 16px; margin-top: 12px; color: var(--accent);">
                contact@insociable.local
            </p>
            <p style="font-size: 14px; color: var(--muted); margin-bottom: 0;">
                (Prototype hébergé localement sous licence libre)
            </p>
        </section>

        <div style="margin-top: 40px; border-top: 1px solid var(--border); padding-top: 20px;">
            <a href="{{ route('home') }}">← Retour à l’accueil</a>
        </div>
    </div>
</x-app-layout>
