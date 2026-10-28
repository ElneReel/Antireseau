<x-app-layout>
    <x-slot:title>{{ $profile->effective_name }} (@{{ $profile->handle }}) - Insociable</x-slot:title>

    <div class="reading-container" style="padding-top: 32px; padding-bottom: 60px;">
        <!-- En-tête de profil sobre et élégant -->
        <header class="surface-card" style="margin-bottom: 36px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                <div style="display: flex; gap: 18px; align-items: center;">
                    @if($profile->avatar_path)
                        <img src="{{ route('media.avatar', $targetUser->id) }}" alt="Avatar de {{ $profile->effective_name }}" class="author-avatar" style="width: 72px; height: 72px; font-size: 26px;">
                    @else
                        <div class="author-avatar" style="width: 72px; height: 72px; font-size: 26px;" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr($profile->handle, 0, 1)) }}
                        </div>
                    @endif

                    <div>
                        <h1 style="font-size: 24px; margin-bottom: 4px; font-weight: 600;">
                            {{ $profile->effective_name }}
                        </h1>
                        <div style="color: var(--muted); font-size: 15px;">
                            @&lrm;{{ $profile->handle }}
                        </div>
                    </div>
                </div>

                <!-- Actions profil (Modifier si soi-même, sinon Bloquer / Signaler) -->
                <div>
                    @if($isSelf)
                        <a href="{{ route('settings.edit') }}" class="btn btn-secondary btn-sm">
                            Modifier mon profil
                        </a>
                    @else
                        <div style="display: flex; gap: 8px;">
                            @if($isBlockedByViewer)
                                <form method="POST" action="{{ route('blocks.destroy', $targetUser->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        Débloquer ce compte
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('blocks.store') }}" onsubmit="return confirm('Bloquer ce compte ? Ses contenus disparaîtront de votre fil.');">
                                    @csrf
                                    <input type="hidden" name="target_id" value="{{ $targetUser->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Bloquer
                                    </button>
                                </form>

                                <div x-data="{ open: false }" style="display: inline-block;">
                                    <button type="button" @click="open = !open" class="btn btn-secondary btn-sm">
                                        Signaler
                                    </button>

                                    <div x-show="open" @click.away="open = false" x-cloak
                                         style="position: absolute; right: 20px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; width: 280px; z-index: 30; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-top: 6px;">
                                        <form method="POST" action="{{ route('reports.store') }}">
                                            @csrf
                                            <input type="hidden" name="target_type" value="user">
                                            <input type="hidden" name="target_id" value="{{ $targetUser->id }}">
                                            <div class="form-group" style="margin-bottom: 10px;">
                                                <label class="form-label" style="font-size: 13px;">Motif :</label>
                                                <select name="reason" class="form-select" style="font-size: 13px; min-height: 36px;" required>
                                                    <option value="impersonation">Usurpation d'identité</option>
                                                    <option value="spam">Spam répété</option>
                                                    <option value="harassment">Harcèlement</option>
                                                    <option value="other">Autre infraction</option>
                                                </select>
                                            </div>
                                            <div class="form-group" style="margin-bottom: 10px;">
                                                <textarea name="details" class="form-textarea" placeholder="Précisions..." style="min-height: 50px; font-size: 13px;"></textarea>
                                            </div>
                                            <div style="display: flex; justify-content: space-between;">
                                                <button type="button" @click="open = false" class="btn btn-secondary btn-sm" style="font-size: 12px;">Annuler</button>
                                                <button type="submit" class="btn btn-primary btn-sm" style="font-size: 12px;">Signaler</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($profile->bio)
                <div style="margin-top: 20px; font-size: 16px; line-height: 1.5; color: var(--ink); white-space: pre-wrap;">
                    {!! nl2br(e($profile->bio)) !!}
                </div>
            @endif

            <div style="margin-top: 18px; font-size: 13px; color: var(--muted); border-top: 1px solid var(--border); padding-top: 12px;">
                Membre depuis le {{ $targetUser->created_at->format('d/m/Y') }} &bull; Pas d'abonnés &bull; Pas de statut en ligne
            </div>
        </header>

        <!-- Publications de l'auteur dans l'ordre chronologique strict -->
        <section>
            <h2 style="font-size: 18px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); border-bottom: 1px solid var(--border); padding-bottom: 8px; margin-bottom: 16px;">
                Publications de {{ $profile->effective_name }}
            </h2>

            @forelse($posts as $post)
                <x-post-item :post="$post" />
            @empty
                <div style="padding: 40px 0; text-align: center; color: var(--muted);">
                    Aucune publication partagée par cette personne pour le moment.
                </div>
            @endforelse

            <div style="margin-top: 24px;">
                {{ $posts->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
