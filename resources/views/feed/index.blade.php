<x-app-layout>
    <x-slot:title>antireseau — Fil Vidéos, Photos & Sons</x-slot:title>

    <!-- Sélecteur flottant de Mode de Fil (Flux Vivant vs Chronologique Brut) -->
    <div style="position: absolute; top: 10px; left: 50%; transform: translateX(-50%); z-index: 30; display: flex; background: rgba(9, 10, 15, 0.78); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.12); border-radius: 999px; padding: 3px; box-shadow: 0 8px 25px rgba(0,0,0,0.6);">
        <a href="{{ route('feed', ['mode' => 'bumped']) }}"
           style="padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 800; transition: all 0.2s; {{ ($mode ?? 'bumped') === 'bumped' ? 'background: #ff0055; color: #ffffff; box-shadow: 0 0 12px rgba(255,0,85,0.4);' : 'color: #94a3b8;' }}">
            ⚡ Flux Vivant
        </a>
        <a href="{{ route('feed', ['mode' => 'chrono']) }}"
           style="padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 800; transition: all 0.2s; {{ ($mode ?? 'bumped') === 'chrono' ? 'background: #ffffff; color: #000000;' : 'color: #94a3b8;' }}">
            ⏱️ Chrono Brut
        </a>
    </div>

    <div class="reels-container" id="reelsContainer" x-data="{
        scrollToNext() {
            const container = document.getElementById('reelsContainer');
            container.scrollBy({ top: container.clientHeight, behavior: 'smooth' });
        },
        scrollToPrev() {
            const container = document.getElementById('reelsContainer');
            container.scrollBy({ top: -container.clientHeight, behavior: 'smooth' });
        }
    }" @keydown.window.down="scrollToNext()" @keydown.window.up="scrollToPrev()">

        <!-- Boutons de raccourci Suivant / Précédent flottants (Desktop & Mobile) -->
        <div style="position: fixed; right: 16px; top: 50%; transform: translateY(-50%); display: flex; flex-direction: column; gap: 10px; z-index: 40;">
            <button type="button" @click="scrollToPrev()" class="icon-btn" style="background: rgba(18,20,28,0.7); backdrop-filter: blur(10px); border: 1px solid var(--border-glass);" title="Post précédent (Flèche haut)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
            </button>
            <button type="button" @click="scrollToNext()" class="icon-btn" style="background: rgba(18,20,28,0.7); backdrop-filter: blur(10px); border: 1px solid var(--border-glass);" title="Post suivant (Flèche bas / Molette)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
        </div>

        @if($isSuspended)
            <div style="padding: 20px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: var(--radius-md); margin: 16px; color: #fca5a5; font-size: 14px;">
                ⚠️ <strong>Écriture suspendue pour 24h :</strong> Tu pourras publier à nouveau le {{ $suspendedUntil }}. La lecture reste active.
            </div>
        @endif

        <!-- Publications en cascade Snap Scroll (1 post par écran) -->
        @forelse($posts as $post)
            <x-post-item :post="$post" />
        @empty
            <div class="reel-item" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 24px;">
                <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(255,0,85,0.12); display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#ff0055" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                </div>
                <h2 style="font-size: 22px; margin-bottom: 8px;">Le fil est encore calme</h2>
                <p style="color: var(--text-muted); font-size: 14px; max-width: 280px; margin-bottom: 24px;">
                    Sois le premier à déposer une photo, une vidéo, un extrait audio ou un mot dans le fil commun.
                </p>
                <button type="button" @click="$dispatch('open-create-modal')" class="btn-primary-gradient" style="width: auto; padding: 12px 28px;">
                    Créer la première publication
                </button>
            </div>
        @endforelse

        <!-- Page suivante pour le scroll infini / chargement -->
        @if($nextCursor)
            <div class="reel-item" style="height: 160px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <a href="{{ route('feed', ['mode' => $mode ?? 'bumped', 'before_created_at' => $nextCursor['before_created_at'], 'before_id' => $nextCursor['before_id']]) }}"
                   class="btn-primary-gradient" style="width: auto; padding: 12px 24px; font-size: 14px;">
                    Charger les publications précédentes
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
