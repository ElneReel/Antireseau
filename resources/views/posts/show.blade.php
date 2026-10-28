<x-app-layout>
    <x-slot:title>Publication de {{ $post->user->profile->effective_name }} - Insociable</x-slot:title>

    <div class="reading-container" style="padding-top: 24px; padding-bottom: 60px;">
        <nav style="margin-bottom: 24px;">
            <a href="{{ route('feed') }}" class="no-underline" style="font-size: 14px; color: var(--muted); display: inline-flex; align-items: center; gap: 6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                <span>Retour au fil</span>
            </a>
        </nav>

        <!-- Publication détaillée -->
        <x-post-item :post="$post" :isDetailed="true" />

        <!-- Contrôles spécifiques à l'auteur pour les commentaires -->
        @can('toggleComments', $post)
            <div style="margin-top: 16px; padding: 12px 16px; background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 14px; color: var(--muted);">
                    Statut des commentaires : <strong>{{ $post->comments_allowed ? 'Ouverts' : 'Fermés' }}</strong>
                </span>
                <form method="POST" action="{{ route('posts.toggle_comments', $post->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">
                        {{ $post->comments_allowed ? 'Fermer les réponses' : 'Ouvrir les réponses' }}
                    </button>
                </form>
            </div>
        @endcan

        <!-- Section Commentaires -->
        <section style="margin-top: 40px;">
            <h2 style="font-size: 20px; font-weight: 600; border-bottom: 1px solid var(--border); padding-bottom: 8px; margin-bottom: 24px;">
                Commentaires ({{ $post->comments->count() }})
            </h2>

            <!-- Formulaire de commentaire -->
            @auth
                @if($post->comments_allowed)
                    @if(auth()->user()->isWritingSuspended())
                        <div class="alert alert-suspension" style="font-size: 14px; padding: 12px 16px; margin-bottom: 24px;">
                            Ton compte est suspendu d'écriture pour 24 heures. Tu ne peux pas commenter pour le moment.
                        </div>
                    @else
                        <form method="POST" action="{{ route('comments.store', $post->id) }}" class="surface-card" style="margin-bottom: 32px;"
                              x-data="{ body: '', charLimit: {{ config('insociable.comment_max_length', 1000) }}, key: '{{ Str::random(32) }}' }">
                            @csrf
                            <input type="hidden" name="idempotency_key" :value="key">

                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="comment-body" class="form-label">Ajouter une réflexion</label>
                                <textarea id="comment-body"
                                          name="body"
                                          class="form-textarea"
                                          placeholder="Un commentaire sans invective..."
                                          maxlength="{{ config('insociable.comment_max_length', 1000) }}"
                                          x-model="body"
                                          rows="3"
                                          required></textarea>
                                @error('body')
                                    <div class="form-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <span style="font-size: 13px; color: var(--muted);" :style="body.length > charLimit - 50 ? 'color: var(--danger); font-weight: 600;' : ''">
                                    <span x-text="body.length">0</span> / <span x-text="charLimit">1000</span>
                                </span>
                                <button type="submit" class="btn btn-primary btn-sm" :disabled="body.trim() === ''">
                                    Publier le commentaire
                                </button>
                            </div>
                        </form>
                    @endif
                @else
                    <div style="background-color: var(--surface); border: 1px dashed var(--border); border-radius: var(--radius); padding: 16px; text-align: center; color: var(--muted); font-size: 14px; margin-bottom: 32px;">
                        L’auteur a fermé les commentaires sur cette publication.
                    </div>
                @endif
            @else
                <div style="background-color: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; text-align: center; margin-bottom: 32px; font-size: 14px;">
                    <a href="{{ route('login') }}">Connecte-toi</a> pour participer à la conversation.
                </div>
            @endauth

            <!-- Liste des commentaires (tri chronologique ascendant) -->
            <div class="comments-list">
                @forelse($post->comments as $comment)
                    <article style="padding: 18px 0; border-bottom: 1px solid var(--border); font-size: 15px;" id="commentaire-{{ $comment->id }}">
                        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 6px; flex-wrap: wrap; gap: 8px;">
                            <div class="author-block">
                                <a href="{{ route('profiles.show', $comment->user->profile->handle) }}" class="no-underline">
                                    @if($comment->user->profile->avatar_path)
                                        <img src="{{ route('media.avatar', $comment->user->id) }}" alt="Avatar" class="author-avatar" style="width: 28px; height: 28px; font-size: 12px;">
                                    @else
                                        <div class="author-avatar" style="width: 28px; height: 28px; font-size: 12px;">
                                            {{ mb_strtoupper(mb_substr($comment->user->profile->handle, 0, 1)) }}
                                        </div>
                                    @endif
                                </a>
                                <div>
                                    <a href="{{ route('profiles.show', $comment->user->profile->handle) }}" class="author-name" style="font-size: 14px;">
                                        {{ $comment->user->profile->effective_name }}
                                    </a>
                                    <span class="author-handle" style="font-size: 13px;">@&lrm;{{ $comment->user->profile->handle }}</span>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 12px;">
                                <time datetime="{{ $comment->created_at->toISOString() }}" style="font-size: 12px; color: var(--muted);" title="{{ $comment->created_at->format('d/m/Y à H:i:s') }}">
                                    {{ $comment->created_at->diffForHumans() }}
                                </time>

                                @can('delete', $comment)
                                    <form method="POST" action="{{ route('comments.destroy', $comment->id) }}" style="display: inline;" onsubmit="return confirm('Retirer ce commentaire ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="post-action-btn" style="color: var(--danger); font-size: 12px; padding: 2px 6px; min-height: 28px;" title="Supprimer le commentaire">
                                            Retirer
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>

                        <div style="padding-left: 36px; line-height: 1.55; white-space: pre-wrap; word-break: break-word;">
                            {!! nl2br(e($comment->body)) !!}
                        </div>
                    </article>
                @empty
                    <div style="padding: 24px 0; color: var(--muted); font-size: 14px;">
                        Aucun commentaire pour le moment.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
