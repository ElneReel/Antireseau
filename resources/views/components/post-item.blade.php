@props(['post', 'isDetailed' => false])

<article class="post-item" id="publication-{{ $post->id }}">
    <header class="post-header">
        <div class="author-block">
            <a href="{{ route('profiles.show', $post->user->profile->handle) }}" class="no-underline" aria-label="Profil de {{ $post->user->profile->effective_name }}">
                @if($post->user->profile->avatar_path)
                    <img src="{{ route('media.avatar', $post->user->id) }}" alt="Avatar de {{ $post->user->profile->effective_name }}" class="author-avatar">
                @else
                    <div class="author-avatar" aria-hidden="true">
                        {{ mb_strtoupper(mb_substr($post->user->profile->handle, 0, 1)) }}
                    </div>
                @endif
            </a>
            <div>
                <a href="{{ route('profiles.show', $post->user->profile->handle) }}" class="author-name">
                    {{ $post->user->profile->effective_name }}
                </a>
                <span class="author-handle">@&lrm;{{ $post->user->profile->handle }}</span>
            </div>
        </div>

        <div class="post-date">
            <time datetime="{{ $post->created_at->toISOString() }}" title="{{ $post->created_at->format('d/m/Y à H:i:s') }}">
                {{ $post->created_at->diffForHumans() }}
            </time>
            @if($post->edited_at)
                <span style="font-size: 12px; color: var(--muted); margin-left: 4px;" title="Modifiée le {{ $post->edited_at->format('d/m/Y à H:i') }}">
                    (modifié)
                </span>
            @endif
        </div>
    </header>

    <div class="post-body">
        {!! nl2br(e($post->body)) !!}
    </div>

    @if($post->media)
        <div class="post-image-container">
            <img src="{{ route('media.show', $post->media->id) }}"
                 alt="{{ $post->media->alt_text ?: 'Image jointe à la publication' }}"
                 class="post-image"
                 loading="lazy">
        </div>
    @endif

    <footer class="post-actions" aria-label="Actions sur la publication">
        @if(!$isDetailed)
            <a href="{{ route('posts.show', $post->id) }}" class="post-action-btn" title="Voir la publication et les commentaires">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                <span>{{ $post->comments_count ?? $post->comments()->count() }} commentaire{{ ($post->comments_count ?? $post->comments()->count()) > 1 ? 's' : '' }}</span>
            </a>
        @endif

        @auth
            <!-- Sauvegarde discrète -->
            <form method="POST" action="{{ route('bookmarks.toggle', $post->id) }}" style="display: inline;">
                @csrf
                @php $bookmarked = $post->isBookmarkedBy(auth()->user()); @endphp
                <button type="submit" class="post-action-btn {{ $bookmarked ? 'active' : '' }}" title="{{ $bookmarked ? 'Retirer des sauvegardes' : 'Garder en sauvegarde' }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="{{ $bookmarked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                    <span>{{ $bookmarked ? 'Sauvegardé' : 'Sauvegarder' }}</span>
                </button>
            </form>

            @can('update', $post)
                <a href="{{ route('posts.edit', $post->id) }}" class="post-action-btn" title="Modifier la publication">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    <span>Modifier</span>
                </a>
            @endcan

            @can('delete', $post)
                <form method="POST" action="{{ route('posts.destroy', $post->id) }}" style="display: inline;" onsubmit="return confirm('Es-tu certain de vouloir supprimer cette publication ? Cette action est irréversible.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="post-action-btn" style="color: var(--danger);" title="Supprimer la publication">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        <span>Supprimer</span>
                    </button>
                </form>
            @endcan

            @if(auth()->id() !== $post->user_id)
                <div x-data="{ open: false }" style="display: inline-block;">
                    <button type="button" @click="open = !open" class="post-action-btn" title="Signaler cette publication">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                        <span>Signaler</span>
                    </button>

                    <!-- Mini boîte de signalement accessible -->
                    <div x-show="open" @click.away="open = false" x-cloak
                         style="position: absolute; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; width: 280px; z-index: 30; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-top: 6px;">
                        <form method="POST" action="{{ route('reports.store') }}">
                            @csrf
                            <input type="hidden" name="target_type" value="post">
                            <input type="hidden" name="target_id" value="{{ $post->id }}">
                            <div class="form-group" style="margin-bottom: 10px;">
                                <label class="form-label" style="font-size: 13px;">Motif :</label>
                                <select name="reason" class="form-select" style="font-size: 13px; padding: 6px 8px; min-height: 36px;" required>
                                    <option value="spam">Spam / Répétition abusive</option>
                                    <option value="harassment">Harcèlement ou menace</option>
                                    <option value="illegal">Contenu illicite</option>
                                    <option value="other">Autre non-respect de la charte</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 10px;">
                                <textarea name="details" class="form-textarea" placeholder="Précision facultative..." style="min-height: 50px; font-size: 13px; padding: 6px;"></textarea>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <button type="button" @click="open = false" class="btn btn-secondary btn-sm" style="font-size: 12px; padding: 4px 8px;">Annuler</button>
                                <button type="submit" class="btn btn-primary btn-sm" style="font-size: 12px; padding: 4px 8px;">Envoyer</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endauth
    </footer>
</article>
