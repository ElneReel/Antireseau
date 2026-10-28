<x-app-layout>
    <x-slot:title>Le Fil - Insociable</x-slot:title>

    <div class="reading-container" style="padding-top: 24px; padding-bottom: 60px;">
        <!-- Bannière d'état en cas de suspension d'écriture (24h) -->
        @if($isSuspended)
            <div class="alert alert-suspension" role="alert">
                <div style="font-size: 24px; line-height: 1;" aria-hidden="true">⏳</div>
                <div>
                    <h2 style="font-size: 18px; margin-bottom: 6px; font-weight: 600;">Écriture temporairement suspendue</h2>
                    <p style="margin-bottom: 8px;">
                        Publication suspendue pour 24 h après des envois répétés. Tu pourras publier à nouveau le <strong>{{ $suspendedUntil }}</strong>. Tu peux continuer à lire.
                    </p>
                    @if($suspensionReason)
                        <p style="font-size: 13px; color: var(--muted); margin-bottom: 10px;">
                            Motif : {{ $suspensionReason }}
                        </p>
                    @endif
                    <div>
                        <a href="{{ route('appeals.create') }}" class="btn btn-secondary btn-sm" style="background: white;">
                            Contester cette mesure
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Formulaire compact de publication -->
            <section class="composer-box" id="publier" x-data="{
                body: '{{ old('body', '') }}',
                hasImage: false,
                charLimit: {{ config('insociable.post_max_length', 2000) }},
                idempotencyKey: '{{ Str::random(32) }}',
                updateFile(e) {
                    this.hasImage = e.target.files.length > 0;
                }
            }">
                <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="idempotency_key" :value="idempotencyKey">

                    <label for="post-body-input" class="sr-only" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); border: 0;">
                        Que souhaites-tu partager sur le fil ?
                    </label>

                    <textarea id="post-body-input"
                              name="body"
                              class="composer-textarea"
                              placeholder="Que souhaites-tu déposer dans le fil commun ? (Pas d'algorithme, pas de likes)"
                              maxlength="{{ config('insociable.post_max_length', 2000) }}"
                              x-model="body"
                              rows="3"></textarea>

                    @error('body')
                        <div class="form-error" style="margin-bottom: 10px;">{{ $message }}</div>
                    @enderror

                    <!-- Zone image facultative repliable -->
                    <div style="margin-top: 10px;" x-data="{ showAlt: false }">
                        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                            <label class="btn btn-secondary btn-sm" style="cursor: pointer;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                <span>Ajouter une image</span>
                                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" style="display: none;" @change="updateFile($event); showAlt = true;">
                            </label>
                            <span x-show="hasImage" style="font-size: 13px; color: var(--accent); font-weight: 500;" x-cloak>
                                Image sélectionnée (max 3 Mo)
                            </span>
                        </div>

                        <div x-show="showAlt" style="margin-top: 10px;" x-cloak>
                            <input type="text" name="alt_text" class="form-input" placeholder="Description de l'image (texte alternatif pour accessibilité)" style="font-size: 13px; min-height: 38px;">
                        </div>

                        @error('image')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="composer-footer">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <label class="form-check" style="min-height: auto; margin-bottom: 0;">
                                <input type="checkbox" name="comments_allowed" value="1" checked style="width: 16px; height: 16px;">
                                <span style="font-size: 13px; color: var(--muted);">Autoriser les réponses</span>
                            </label>
                            <span style="font-size: 13px; color: var(--muted);" :style="body.length > charLimit - 100 ? 'color: var(--danger); font-weight: 600;' : ''">
                                <span x-text="body.length">0</span> / <span x-text="charLimit">2000</span>
                            </span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm" :disabled="body.trim() === '' && !hasImage">
                            Publier sur le fil
                        </button>
                    </div>
                </form>
            </section>
        @endif

        <!-- En-tête du fil et bouton d'actualisation volontaire -->
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid var(--ink); padding-bottom: 12px; margin-top: 36px; margin-bottom: 8px;">
            <div>
                <h1 style="font-size: 20px; font-weight: 600; margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">
                    Fil commun
                </h1>
                <div style="font-size: 13px; color: var(--muted);">
                    Ordre chronologique strict &bull; Du plus récent au plus ancien
                </div>
            </div>

            <a href="{{ route('feed') }}" class="btn btn-secondary btn-sm" title="Vérifier les nouvelles publications sans rechargement forcé">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;" aria-hidden="true"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                <span>Actualiser le fil</span>
            </a>
        </div>

        <!-- Liste des publications -->
        @forelse($posts as $post)
            <x-post-item :post="$post" />
        @empty
            <div style="padding: 60px 0; text-align: center; color: var(--muted);">
                <p style="font-family: var(--font-serif); font-size: 20px;">Le fil est silencieux pour le moment.</p>
                <p style="font-size: 15px;">Dépose les premiers mots ci-dessus.</p>
            </div>
        @endforelse

        <!-- Pagination par curseur stable -->
        @if($nextCursor)
            <div style="margin-top: 36px; text-align: center;">
                <a href="{{ route('feed', ['before_created_at' => $nextCursor['before_created_at'], 'before_id' => $nextCursor['before_id']]) }}"
                   class="btn btn-secondary" style="padding: 12px 24px; font-size: 15px;">
                    Voir les publications précédentes
                </a>
                <div style="font-size: 13px; color: var(--muted); margin-top: 8px;">
                    Pagination stable sans saut de contenu
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
