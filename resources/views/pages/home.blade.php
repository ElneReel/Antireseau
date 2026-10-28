<x-app-layout>
    <x-slot:title>Insociable - Personne à suivre. Tout le monde ici.</x-slot:title>

    <div class="reading-container" style="padding-top: 40px; padding-bottom: 60px;">
        <header style="margin-bottom: 48px;">
            <h1 class="hero-title">Personne à suivre.<br>Tout le monde ici.</h1>
            <p style="font-size: 21px; line-height: 1.45; color: var(--muted); font-family: var(--font-serif); max-width: 620px;">
                Des publications dans l’ordre. Pas dans l’ordre qui arrange un algorithme.
            </p>
        </header>

        <section style="margin-bottom: 48px; border-left: 2px solid var(--accent); padding-left: 24px;">
            <p style="font-size: 18px; line-height: 1.6;">
                Ici, aucun compteur de followers. Pas de réseau d’amis, pas de calcul de réputation, pas d’engagement artificiel pour voler ton attention.
            </p>
            <p style="font-size: 18px; line-height: 1.6; color: var(--ink);">
                Tout le monde écrit dans un fil commun, strictement chronologique, du plus récent au plus ancien. Ce que tu lis dépend uniquement de l'heure à laquelle cela a été écrit.
            </p>
            <div style="margin-top: 28px; display: flex; gap: 16px; flex-wrap: wrap;">
                <a href="{{ route('register') }}" class="btn btn-primary" style="font-size: 16px; padding: 12px 24px;">
                    Entrer sans faire de bruit
                </a>
                <a href="{{ route('login') }}" class="btn btn-secondary" style="font-size: 16px; padding: 12px 24px;">
                    Déjà membre ? Se connecter
                </a>
            </div>
        </section>

        <!-- Aperçu de démonstration clairement étiqueté "exemple" -->
        <section style="margin-top: 64px;">
            <div style="display: flex; align-items: baseline; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px; margin-bottom: 16px;">
                <h2 style="font-size: 18px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); margin: 0;">
                    À quoi ressemble le lieu
                </h2>
                <span style="font-size: 12px; background: var(--accent-soft); color: var(--accent); padding: 2px 8px; border-radius: 4px; font-weight: 500;">
                    Exemple de démonstration
                </span>
            </div>

            <article class="post-item" style="border-bottom: 1px solid var(--border);">
                <header class="post-header">
                    <div class="author-block">
                        <div class="author-avatar" aria-hidden="true">C</div>
                        <div>
                            <span class="author-name">Camille</span>
                            <span class="author-handle">@camille</span>
                        </div>
                    </div>
                    <div class="post-date">
                        <span>Il y a 14 minutes</span>
                    </div>
                </header>
                <div class="post-body">
                    Rien à vendre. Rien à optimiser pour plaire à une machine. Juste l'envie d'écrire trois phrases sans penser à l'audience.
                </div>
            </article>

            <article class="post-item">
                <header class="post-header">
                    <div class="author-block">
                        <div class="author-avatar" aria-hidden="true">J</div>
                        <div>
                            <span class="author-name">Julien</span>
                            <span class="author-handle">@julien</span>
                        </div>
                    </div>
                    <div class="post-date">
                        <span>Il y a 32 minutes</span>
                    </div>
                </header>
                <div class="post-body">
                    Le silence entre les mots est aussi important que le texte lui-même. Bonne fin de journée à tous.
                </div>
            </article>
        </section>
    </div>
</x-app-layout>
