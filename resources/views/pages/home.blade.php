<x-app-layout>
    <x-slot:title>antireseau — 0 Abonné, 0 Influenceur, Égalité Totale</x-slot:title>

    <div style="flex: 1; overflow-y: auto; padding: 24px 20px 60px; display: flex; flex-direction: column; justify-content: space-between; text-align: center;">
        <div>
            <!-- Logo AR Héroïque -->
            <div style="margin-bottom: 16px; display: flex; justify-content: center;">
                <img src="{{ asset('images/logo-ar.svg') }}" alt="antireseau AR" width="68" height="68" style="border-radius: 18px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8), 0 0 25px rgba(255, 0, 85, 0.25);">
            </div>

            <!-- Badge Manifeste -->
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255, 0, 85, 0.12); border: 1px solid rgba(255, 0, 85, 0.35); padding: 5px 14px; border-radius: var(--radius-full); font-size: 11px; font-weight: 800; color: #ff2d55; margin-bottom: 18px; letter-spacing: 0.03em; text-transform: uppercase;">
                <span>⚡ L'Anti-Réseau Social</span>
            </div>

            <!-- Grand Titre Radical -->
            <h1 style="font-size: 34px; line-height: 1.08; font-weight: 900; letter-spacing: -0.04em; margin-bottom: 14px; color: #ffffff;">
                Tuez le Follow.<br>
                <span style="color: #ff0055;">Zéro abonné. Zéro star.</span>
            </h1>

            <p style="font-size: 14px; color: #94a3b8; line-height: 1.5; max-width: 390px; margin: 0 auto 24px;">
                Ici, il est <strong style="color: #ffffff;">impossible de suivre quelqu'un</strong>. Pas d'influenceurs, pas de course aux abonnés. Vous partagez, vous voyez ce que les autres partagent, à égalité absolue.
            </p>

            <!-- Les 3 Lois d'antireseau -->
            <div style="display: flex; flex-direction: column; gap: 10px; max-width: 390px; margin: 0 auto 28px; text-align: left;">
                <!-- Loi 1 -->
                <div style="background: #11131c; border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 14px 16px; display: flex; gap: 12px; align-items: flex-start;">
                    <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(255, 0, 85, 0.15); color: #ff0055; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 15px; flex-shrink: 0;">
                        🚫
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 13px; color: #ffffff; margin-bottom: 2px;">Aucun bouton « Suivre »</div>
                        <div style="font-size: 12px; color: #94a3b8; line-height: 1.4;">Tout le monde a <strong>0 abonné</strong>. Un inconnu qui publie a exactement la même chance d'être vu que n'importe qui.</div>
                    </div>
                </div>

                <!-- Loi 2 -->
                <div style="background: #11131c; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 14px; padding: 14px 16px; display: flex; gap: 12px; align-items: flex-start;">
                    <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 15px; flex-shrink: 0;">
                        🔁
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 13px; color: #34d399; margin-bottom: 2px;">Le Repartage fait remonter le post</div>
                        <div style="font-size: 12px; color: #94a3b8; line-height: 1.4;">Une publication vous plaît ? <strong>Repartagez-la</strong> : elle remonte immédiatement tout en haut du fil commun.</div>
                    </div>
                </div>

                <!-- Loi 3 -->
                <div style="background: #11131c; border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 14px 16px; display: flex; gap: 12px; align-items: flex-start;">
                    <div style="width: 34px; height: 34px; border-radius: 10px; background: rgba(139, 92, 246, 0.15); color: #c4b5fd; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 15px; flex-shrink: 0;">
                        ❤️
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 13px; color: #ffffff; margin-bottom: 2px;">Likes, Commentaires & Profils ouverts</div>
                        <div style="font-size: 12px; color: #94a3b8; line-height: 1.4;">Sur chaque profil, découvrez ce que la personne crée, ce qu'elle repartage et ce qu'elle like.</div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px; max-width: 390px; margin: 0 auto; width: 100%;">
            <a href="{{ route('register') }}" class="btn-primary-gradient" style="background: #ff0055; box-shadow: 0 8px 25px rgba(255, 0, 85, 0.4);">
                Entrer sur antireseau
            </a>
            <a href="{{ route('login') }}" class="icon-btn" style="width: 100%; border-radius: var(--radius-full); font-size: 14px; font-weight: 700; height: 46px; border: 1px solid var(--border-subtle);">
                Déjà membre ? Se connecter
            </a>
        </div>
    </div>
</x-app-layout>
