<x-app-layout>
    <x-slot:title>Mes Sauvegardes — Insociable</x-slot:title>

    <div style="flex: 1; overflow-y: auto; padding: 20px 16px 80px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px;">
            <h1 style="font-size: 20px; font-weight: 800;">Sauvegardes ({{ $bookmarks->total() }})</h1>
            <span style="font-size: 12px; color: var(--text-muted);">Privé &bull; Non visible</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            @forelse($bookmarks as $bookmark)
                @php $post = $bookmark->post; @endphp
                <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 16px; position: relative;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <a href="{{ route('profiles.show', $post->user->profile->handle) }}" style="display: flex; align-items: center; gap: 8px;">
                            <div class="author-chip-avatar" style="width: 28px; height: 28px; font-size: 11px;">
                                {{ mb_strtoupper(mb_substr($post->user->profile->handle, 0, 1)) }}
                            </div>
                            <span style="font-weight: 700; font-size: 13px; color: white;">{{ $post->user->profile->effective_name }}</span>
                        </a>
                        <span style="font-size: 11px; color: var(--text-muted);">{{ $post->created_at->diffForHumans() }}</span>
                    </div>

                    @if($post->body)
                        <div style="font-size: 14px; color: #e2e8f0; line-height: 1.45; margin-bottom: 10px;">
                            {!! nl2br(e($post->body)) !!}
                        </div>
                    @endif

                    @if($post->media && $post->media->isImage())
                        <img src="{{ route('media.show', $post->media->id) }}" style="width: 100%; max-height: 240px; object-fit: cover; border-radius: var(--radius-sm); margin-bottom: 10px;">
                    @elseif($post->media && $post->media->isVideo())
                        <video src="{{ route('media.show', $post->media->id) }}" controls style="width: 100%; max-height: 240px; border-radius: var(--radius-sm); margin-bottom: 10px;"></video>
                    @elseif($post->media && $post->media->isAudio())
                        <audio src="{{ route('media.show', $post->media->id) }}" controls style="width: 100%; margin-bottom: 10px;"></audio>
                    @endif

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-subtle); padding-top: 8px; margin-top: 6px;">
                        <a href="{{ route('posts.show', $post->id) }}" style="font-size: 12px; color: var(--accent); font-weight: 600;">
                            Ouvrir &bull; {{ $post->comments->count() }} commentaires
                        </a>
                        <form method="POST" action="{{ route('bookmarks.toggle', $post->id) }}">
                            @csrf
                            <button type="submit" style="font-size: 12px; color: var(--danger); background: none;">Retirer</button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 60px 20px;">
                    <div style="font-size: 40px; margin-bottom: 12px;">🔖</div>
                    <h3 style="font-size: 16px; margin-bottom: 6px; color: white;">Aucune sauvegarde</h3>
                    <p style="font-size: 13px;">Cliquez sur l'icône marque-page dans le fil pour garder vos publications préférées.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
