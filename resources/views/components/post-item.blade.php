@props(['post', 'isDetailed' => false])

<section class="reel-item" id="post-{{ $post->id }}" x-data="{
    commentsOpen: false,
    optionsOpen: false,
    liked: {{ $post->isLikedBy(auth()->user()) ? 'true' : 'false' }},
    likesCount: {{ $post->likes_count ?? $post->likes()->count() }},
    reposted: {{ $post->isRepostedBy(auth()->user()) ? 'true' : 'false' }},
    repostsCount: {{ $post->reposts_count ?? $post->reposts()->count() }},
    reposterHandle: '{{ $post->lastRepostedBy?->profile?->handle ?? '' }}',
    reposterName: '{{ addslashes($post->lastRepostedBy?->profile?->effective_name ?? '') }}',
    bookmarked: {{ $post->isBookmarkedBy(auth()->user()) ? 'true' : 'false' }},
    showHeartBurst: false,
    showRepostToast: false,
    isPlaying: true,
    isMuted: true,
    toggleLike() {
        this.liked = !this.liked;
        this.likesCount += this.liked ? 1 : -1;
        if (this.liked) {
            this.showHeartBurst = true;
            setTimeout(() => this.showHeartBurst = false, 800);
        }
        fetch('{{ route('posts.like', $post->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(data => {
            this.liked = data.liked;
            this.likesCount = data.likes_count;
        });
    },
    doubleTapLike() {
        if (!this.liked) {
            this.toggleLike();
        } else {
            this.showHeartBurst = true;
            setTimeout(() => this.showHeartBurst = false, 800);
        }
    },
    toggleRepost() {
        this.reposted = !this.reposted;
        this.repostsCount += this.reposted ? 1 : -1;
        if (this.reposted) {
            this.showRepostToast = true;
            setTimeout(() => this.showRepostToast = false, 2800);
        }
        fetch('{{ route('posts.repost', $post->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(r => r.json()).then(data => {
            this.reposted = data.reposted;
            this.repostsCount = data.reposts_count;
            if (data.reposter_handle) {
                this.reposterHandle = data.reposter_handle;
                this.reposterName = data.reposter_name;
            }
        });
    },
    toggleBookmark() {
        this.bookmarked = !this.bookmarked;
        fetch('{{ route('bookmarks.toggle', $post->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
    },
    toggleVideo(el) {
        if (el.paused) {
            el.play();
            this.isPlaying = true;
        } else {
            el.pause();
            this.isPlaying = false;
        }
    },
    toggleAudio(audioEl) {
        if (audioEl.paused) {
            audioEl.play();
            this.isPlaying = true;
        } else {
            audioEl.pause();
            this.isPlaying = false;
        }
    }
}">
    <!-- Bandeau supérieur "Remonté dans le fil par..." (quand repartagé) -->
    <div x-show="reposterHandle !== ''" x-cloak
         style="position: absolute; top: 14px; left: 16px; z-index: 25; display: inline-flex; align-items: center; gap: 6px; background: rgba(16, 185, 129, 0.16); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(16, 185, 129, 0.4); padding: 5px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; color: #34d399; box-shadow: 0 4px 15px rgba(0,0,0,0.4);">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
        <span>Remonté par</span>
        <a :href="'/membres/' + reposterHandle" style="color: #ffffff; text-decoration: underline;" x-text="'@' + reposterHandle"></a>
    </div>

    <!-- Notification Toast quand on repartage / fait remonter -->
    <div x-show="showRepostToast" x-transition.opacity x-cloak
         style="position: absolute; top: 56px; left: 50%; transform: translateX(-50%); z-index: 45; background: #10b981; color: #052e16; font-weight: 800; font-size: 12px; padding: 8px 16px; border-radius: 999px; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5); white-space: nowrap; display: flex; align-items: center; gap: 6px;">
        <span>⚡ Propulsé en haut du fil commun !</span>
    </div>

    <!-- Animation Grand Cœur au Double-Tap / Like -->
    <div x-show="showHeartBurst" x-transition.scale.origin.center x-cloak
         style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; z-index: 35; pointer-events: none;">
        <svg width="96" height="96" viewBox="0 0 24 24" fill="#ff0055" stroke="#ffffff" stroke-width="1" style="filter: drop-shadow(0 0 30px rgba(255, 0, 85, 0.8));">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
        </svg>
    </div>

    <!-- Média principal (Double-clic ou Double-tap = Like) -->
    <div class="media-display-wrapper" @dblclick="doubleTapLike()">
        @if($post->media && $post->media->isVideo())
            <!-- Format Vidéo Plein Format (Reels / TikTok) -->
            <div class="video-wrapper">
                <video class="video-element"
                       src="{{ route('media.show', $post->media->id) }}"
                       playsinline
                       loop
                       autoplay
                       muted
                       x-ref="videoPlayer"
                       @click="toggleVideo($refs.videoPlayer)">
                </video>

                <!-- Bouton Mute/Unmute flottant -->
                <button type="button"
                        @click.stop="$refs.videoPlayer.muted = !$refs.videoPlayer.muted; isMuted = $refs.videoPlayer.muted"
                        style="position: absolute; top: 16px; right: 16px; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; z-index: 5;">
                    <template x-if="isMuted">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="1" y1="1" x2="23" y2="23"></line><path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V4a3 3 0 0 0-5.94-.6"></path><path d="M17 16.95A7 7 0 0 1 5 12v-2m14 0v2a7 7 0 0 1-.11 1.23"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line></svg>
                    </template>
                    <template x-if="!isMuted">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                    </template>
                </button>
            </div>

        @elseif($post->media && $post->media->isAudio())
            <!-- Format Audio Immersif avec Vinyle et Visualiseur -->
            <div class="audio-card-wrapper">
                <div class="audio-vinyl" :class="{ 'paused': !isPlaying }">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#ff0055" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
                
                <h3 style="font-size: 18px; margin-bottom: 6px; font-weight: 700;">{{ $post->media->original_name }}</h3>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 18px;">Piste audio &bull; antireseau Sound</p>

                <audio x-ref="audioPlayer" src="{{ route('media.show', $post->media->id) }}" @ended="isPlaying = false"></audio>

                <button type="button" class="create-btn-glow" @click="toggleAudio($refs.audioPlayer)" style="width: 56px; height: 56px; margin-bottom: 8px;">
                    <template x-if="!isPlaying">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                    </template>
                    <template x-if="isPlaying">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>
                    </template>
                </button>
            </div>

        @elseif($post->media && $post->media->isImage())
            <!-- Format Photo Haute Définition avec Arrière-Plan d'Ambiance -->
            <div class="ambient-backdrop" style="background-image: url('{{ route('media.show', $post->media->id) }}');"></div>
            <img class="media-element"
                 src="{{ route('media.show', $post->media->id) }}"
                 alt="{{ $post->media->alt_text ?: 'Photo partagée' }}"
                 loading="lazy">

        @else
            <!-- Format Texte / Pensée Seule (Typographie Impactante) -->
            <div class="text-quote-wrapper">
                <div class="quote-content">
                    {!! nl2br(e($post->body)) !!}
                </div>
            </div>
        @endif
    </div>

    <!-- Informations en superposition (Bas Gauche) -->
    <div class="reel-overlay-info">
        <div class="author-chip">
            <a href="{{ route('profiles.show', $post->user->profile->handle) }}" class="author-chip-avatar">
                @if($post->user->profile->avatar_path)
                    <img src="{{ route('media.avatar', $post->user->id) }}" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                @else
                    {{ mb_strtoupper(mb_substr($post->user->profile->handle, 0, 1)) }}
                @endif
            </a>
            <div style="display: flex; flex-direction: column;">
                <a href="{{ route('profiles.show', $post->user->profile->handle) }}" class="author-chip-name" style="line-height: 1.1;">
                    {{ $post->user->profile->effective_name }}
                </a>
                <span style="font-size: 11px; color: rgba(255,255,255,0.65);">@&lrm;{{ $post->user->profile->handle }} &bull; 0 abonné</span>
            </div>
        </div>

        @if($post->media && $post->body)
            <div class="reel-caption">
                {!! nl2br(e($post->body)) !!}
            </div>
        @endif

        <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
            <span class="reel-meta-tag">
                @if($post->media && $post->media->isVideo()) 🎬 Vidéo
                @elseif($post->media && $post->media->isAudio()) 🎙️ Audio
                @elseif($post->media && $post->media->isImage()) 📸 Photo
                @else ✍️ Pensée
                @endif
            </span>
            <span style="font-size: 12px; color: rgba(255, 255, 255, 0.6); text-shadow: 0 1px 3px rgba(0,0,0,0.8);">
                {{ $post->created_at->diffForHumans() }}
            </span>
            @if($post->edited_at)
                <span style="font-size: 11px; color: var(--accent);">(modifié)</span>
            @endif
        </div>
    </div>

    <!-- Dock d'actions latéral droit (Like, Repartager/Remonter, Commenter, Sauver) -->
    <div class="reel-actions-dock">
        <!-- 1. LIKE (Cœur) -->
        <button type="button" class="reel-action-btn" @click="toggleLike()" title="Aimer cette publication">
            <div class="reel-action-circle" :style="liked ? 'background: rgba(255, 0, 85, 0.2); border-color: #ff0055; color: #ff0055;' : ''">
                <svg width="22" height="22" viewBox="0 0 24 24" :fill="liked ? '#ff0055' : 'none'" :stroke="liked ? '#ff0055' : 'currentColor'" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </div>
            <span class="reel-action-label" x-text="likesCount"></span>
        </button>

        <!-- 2. REPARTAGER / FAIRE REMONTER EN TÊTE DU FIL -->
        <button type="button" class="reel-action-btn" @click="toggleRepost()" title="Repartager pour faire remonter ce post en haut du fil">
            <div class="reel-action-circle" :style="reposted ? 'background: rgba(16, 185, 129, 0.22); border-color: #10b981; color: #10b981; box-shadow: 0 0 15px rgba(16,185,129,0.4);' : ''">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="reposted ? '#10b981' : 'currentColor'" stroke-width="2.2">
                    <polyline points="17 1 21 5 17 9"></polyline>
                    <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
                    <polyline points="7 23 3 19 7 15"></polyline>
                    <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
                </svg>
            </div>
            <span class="reel-action-label" :style="reposted ? 'color: #34d399; font-weight: 800;' : ''" x-text="repostsCount > 0 ? repostsCount : 'Remonter'"></span>
        </button>

        <!-- 3. COMMENTAIRES (Ouvre la Bottom Sheet) -->
        <button type="button" class="reel-action-btn" @click="commentsOpen = true" title="Lire et écrire des commentaires">
            <div class="reel-action-circle">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                </svg>
            </div>
            <span class="reel-action-label">{{ $post->comments_count ?? $post->comments->count() }}</span>
        </button>

        <!-- 4. SAUVEGARDE PRIVÉE -->
        <button type="button" class="reel-action-btn" @click="toggleBookmark()" title="Garder dans mes favoris privés">
            <div class="reel-action-circle" :class="{ 'active': bookmarked }">
                <svg width="20" height="20" viewBox="0 0 24 24" :fill="bookmarked ? '#ffffff' : 'none'" stroke="currentColor" stroke-width="2">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
            <span class="reel-action-label" x-text="bookmarked ? 'Gardé' : 'Sauver'">Sauver</span>
        </button>

        <!-- 5. MENU OPTIONS (...) -->
        <div style="position: relative;">
            <button type="button" class="reel-action-btn" @click="optionsOpen = !optionsOpen">
                <div class="reel-action-circle" style="width: 38px; height: 38px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="12" cy="5" r="1"></circle>
                        <circle cx="12" cy="19" r="1"></circle>
                    </svg>
                </div>
            </button>

            <!-- Menu popover options -->
            <div x-show="optionsOpen" @click.away="optionsOpen = false" x-cloak
                 style="position: absolute; right: 54px; bottom: 0; width: 220px; background: #161822; border: 1px solid var(--border-glass); border-radius: var(--radius-md); padding: 8px; z-index: 50; box-shadow: 0 10px 30px rgba(0,0,0,0.8);">
                <a href="{{ route('posts.show', $post->id) }}" class="nav-link" style="padding: 8px 12px; font-size: 13px; border-radius: 6px; display: block; color: #ffffff;">
                    🔗 Ouvrir la page dédiée
                </a>

                @can('update', $post)
                    <a href="{{ route('posts.edit', $post->id) }}" class="nav-link" style="padding: 8px 12px; font-size: 13px; border-radius: 6px; display: block; color: var(--accent);">
                        ✏️ Modifier le texte
                    </a>
                @endcan

                @can('delete', $post)
                    <form method="POST" action="{{ route('posts.destroy', $post->id) }}" onsubmit="return confirm('Supprimer définitivement ce post ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="width: 100%; text-align: left; padding: 8px 12px; font-size: 13px; color: var(--danger); border-radius: 6px; background: none;">
                            🗑️ Supprimer
                        </button>
                    </form>
                @endcan

                @if(auth()->id() !== $post->user_id)
                    <form method="POST" action="{{ route('reports.store') }}" style="margin-top: 4px;">
                        @csrf
                        <input type="hidden" name="target_type" value="post">
                        <input type="hidden" name="target_id" value="{{ $post->id }}">
                        <input type="hidden" name="reason" value="spam">
                        <button type="submit" style="width: 100%; text-align: left; padding: 8px 12px; font-size: 13px; color: #fca5a5; background: none;">
                            🚩 Signaler comme spam
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Bottom Sheet Coulissante des Commentaires -->
    <div class="bottom-sheet" x-show="commentsOpen" x-cloak @click.self="commentsOpen = false">
        <div class="sheet-panel">
            <div class="sheet-header">
                <div class="sheet-drag-handle"></div>
                <h3 style="font-size: 15px; font-weight: 700;">Commentaires ({{ $post->comments->count() }})</h3>
                <button type="button" @click="commentsOpen = false" style="color: var(--text-muted); font-size: 20px; padding: 4px;">&times;</button>
            </div>

            <div class="sheet-body">
                @forelse($post->comments as $comment)
                    <div style="display: flex; gap: 12px; margin-bottom: 16px; font-size: 14px;">
                        <a href="{{ route('profiles.show', $comment->user->profile->handle) }}" class="author-chip-avatar" style="width: 28px; height: 28px; font-size: 11px; flex-shrink: 0;">
                            {{ mb_strtoupper(mb_substr($comment->user->profile->handle, 0, 1)) }}
                        </a>
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 2px;">
                                <a href="{{ route('profiles.show', $comment->user->profile->handle) }}" style="font-weight: 700; font-size: 13px; color: #ffffff;">{{ $comment->user->profile->effective_name }}</a>
                                <span style="font-size: 11px; color: var(--text-muted);">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="color: #cbd5e1; line-height: 1.4; word-break: break-word;">
                                {!! nl2br(e($comment->body)) !!}
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; color: var(--text-muted); padding: 30px 0; font-size: 14px;">
                        Aucun commentaire pour le moment.<br>Soyez le premier à réagir.
                    </div>
                @endforelse
            </div>

            @if($post->comments_allowed)
                <div class="sheet-footer">
                    <form method="POST" action="{{ route('comments.store', $post->id) }}" style="display: flex; gap: 8px;">
                        @csrf
                        <input type="text"
                               name="body"
                               placeholder="Ajouter un commentaire..."
                               required
                               maxlength="1000"
                               class="creator-input"
                               style="padding: 10px 14px; font-size: 14px; border-radius: var(--radius-full); background: #0c0e14;">
                        <button type="submit" class="btn-primary-gradient" style="width: auto; padding: 0 18px; border-radius: var(--radius-full);">
                            Envoyer
                        </button>
                    </form>
                </div>
            @else
                <div style="padding: 12px; text-align: center; font-size: 13px; color: var(--text-muted); background: #181b25;">
                    L'auteur a fermé les commentaires.
                </div>
            @endif
        </div>
    </div>
</section>
