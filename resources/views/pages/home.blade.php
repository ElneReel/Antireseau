<x-app-layout>
    <x-slot:title>Insociable — Le réseau sans algorithme</x-slot:title>

    <div style="flex: 1; overflow-y: auto; padding: 36px 20px 60px; display: flex; flex-direction: column; justify-content: space-between; text-align: center;">
        <div>
            <!-- Badge chic -->
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(139, 92, 246, 0.12); border: 1px solid rgba(139, 92, 246, 0.3); padding: 6px 14px; border-radius: var(--radius-full); font-size: 12px; font-weight: 700; color: #c4b5fd; margin-bottom: 24px;">
                <span class="badge-live">LIVE</span>
                <span>Fil Chronologique Pur &bull; 0 Algo</span>
            </div>

            <!-- Grand Titre Moderne Dégradé -->
            <h1 style="font-size: 38px; line-height: 1.1; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 16px;">
                Personne à suivre.<br>
                <span style="background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                    Tout le monde ici.
                </span>
            </h1>

            <p style="font-size: 16px; color: var(--text-secondary); line-height: 1.5; max-width: 380px; margin: 0 auto 32px;">
                Des vidéos, des photos, de la musique et des pensées dans l'ordre exact de création. Pas dans l'ordre qui arrange une machine.
            </p>

            <!-- Mockup Visuel Interactif -->
            <div style="background: linear-gradient(145deg, #161824, #0f1017); border: 1px solid var(--border-glass); border-radius: var(--radius-lg); padding: 24px 20px; max-width: 380px; margin: 0 auto 36px; text-align: left; box-shadow: 0 20px 40px rgba(0,0,0,0.6);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;">C</div>
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: white;">Camille</div>
                        <div style="font-size: 11px; color: var(--text-muted);">Il y a 10 min &bull; 📸 Photo</div>
                    </div>
                </div>
                <div style="font-size: 14px; color: #cbd5e1; line-height: 1.45; margin-bottom: 14px;">
                    Ici, pas de likes artificiels, pas de course aux abonnés. Un post, le suivant.
                </div>
                <div style="height: 140px; border-radius: var(--radius-md); background: linear-gradient(135deg, #4338ca, #6d28d9, #be185d); display: flex; align-items: center; justify-content: center; font-size: 32px;">
                    ✨
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px; max-width: 380px; margin: 0 auto; width: 100%;">
            <a href="{{ route('register') }}" class="btn-primary-gradient">
                Rejoindre l'application
            </a>
            <a href="{{ route('login') }}" class="icon-btn" style="width: 100%; border-radius: var(--radius-full); font-size: 14px; font-weight: 600; height: 46px; border: 1px solid var(--border-subtle);">
                Déjà membre ? Se connecter
            </a>
        </div>
    </div>
</x-app-layout>
