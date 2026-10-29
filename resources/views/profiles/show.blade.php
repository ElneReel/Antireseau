<x-app-layout>
    <x-slot:title>{{ $profile->effective_name }} (@{{ $profile->handle }}) — Insociable</x-slot:title>

    <div style="flex: 1; overflow-y: auto; padding: 20px 16px 80px;">
        <!-- Carte Profil Style Instagram -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-glass); border-radius: var(--radius-lg); padding: 24px 20px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 16px;">
                <div style="width: 72px; height: 72px; border-radius: 50%; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800; color: white; flex-shrink: 0; box-shadow: 0 0 20px var(--accent-glow);">
                    @if($profile->avatar_path)
                        <img src="{{ route('media.avatar', $targetUser->id) }}" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    @else
                        {{ mb_strtoupper(mb_substr($profile->handle, 0, 1)) }}
                    @endif
                </div>

                <div style="flex: 1;">
                    <h1 style="font-size: 20px; font-weight: 800; color: white; margin-bottom: 2px;">{{ $profile->effective_name }}</h1>
                    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">@&lrm;{{ $profile->handle }}</div>
                    
                    @if($isSelf)
                        <a href="{{ route('settings.edit') }}" class="icon-btn" style="width: 100%; border-radius: var(--radius-full); font-size: 12px; font-weight: 600; height: 34px; border: 1px solid var(--border-subtle);">
                            Modifier le profil
                        </a>
                    @else
                        <div style="display: flex; gap: 8px;">
                            @if($isBlockedByViewer)
                                <form method="POST" action="{{ route('blocks.destroy', $targetUser->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn" style="width: 100%; border-radius: var(--radius-full); font-size: 12px; height: 34px;">
                                        Débloquer
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('blocks.store') }}" onsubmit="return confirm('Bloquer ce profil ?');">
                                    @csrf
                                    <input type="hidden" name="target_id" value="{{ $targetUser->id }}">
                                    <button type="submit" class="icon-btn" style="border-radius: var(--radius-full); font-size: 12px; height: 34px; color: var(--danger); padding: 0 14px; width: auto;">
                                        Bloquer
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($profile->bio)
                <div style="font-size: 14px; color: #cbd5e1; line-height: 1.5; margin-bottom: 14px; white-space: pre-wrap;">
                    {!! nl2br(e($profile->bio)) !!}
                </div>
            @endif

            <div style="display: flex; gap: 16px; border-top: 1px solid var(--border-subtle); padding-top: 12px; font-size: 12px; color: var(--text-muted);">
                <span>📅 Membre depuis {{ $targetUser->created_at->format('M Y') }}</span>
                <span>✨ 0 abonné &bull; Fil pur</span>
            </div>
        </div>

        <!-- Grille des Publications de l'auteur (Instagram Grid) -->
        <h2 style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 14px;">
            Publications ({{ $posts->total() }})
        </h2>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
            @forelse($posts as $post)
                <a href="{{ route('posts.show', $post->id) }}" style="aspect-ratio: 1; border-radius: var(--radius-sm); overflow: hidden; background: #161822; position: relative; display: flex; align-items: center; justify-content: center; text-align: center; border: 1px solid var(--border-subtle);">
                    @if($post->media && $post->media->isImage())
                        <img src="{{ route('media.show', $post->media->id) }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @elseif($post->media && $post->media->isVideo())
                        <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; font-size: 20px;">🎬</div>
                        <video src="{{ route('media.show', $post->media->id) }}" style="width: 100%; height: 100%; object-fit: cover;"></video>
                    @elseif($post->media && $post->media->isAudio())
                        <div style="font-size: 24px;">🎵</div>
                    @else
                        <div style="padding: 8px; font-size: 11px; color: #cbd5e1; line-height: 1.3; overflow: hidden; text-overflow: ellipsis;">
                            {{ Str::limit($post->body, 50) }}
                        </div>
                    @endif
                </a>
            @empty
                <div style="grid-column: span 3; text-align: center; color: var(--text-muted); padding: 40px 0; font-size: 13px;">
                    Aucune publication partagée par cet utilisateur.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
