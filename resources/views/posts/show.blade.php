<x-app-layout>
    <x-slot:title>Publication de {{ $post->user->profile->effective_name }}</x-slot:title>

    <div style="flex: 1; overflow-y: auto; padding: 16px 16px 80px;">
        <nav style="margin-bottom: 16px;">
            <a href="{{ route('feed') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                <span>Retour au fil plein écran</span>
            </a>
        </nav>

        <!-- Carte post détaillée -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-glass); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 24px;">
            <div style="padding: 16px; display: flex; align-items: center; justify-content: space-between;">
                <a href="{{ route('profiles.show', $post->user->profile->handle) }}" style="display: flex; align-items: center; gap: 10px;">
                    <div class="author-chip-avatar">
                        @if($post->user->profile->avatar_path)
                            <img src="{{ route('media.avatar', $post->user->id) }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        @else
                            {{ mb_strtoupper(mb_substr($post->user->profile->handle, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: white;">{{ $post->user->profile->effective_name }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">@&lrm;{{ $post->user->profile->handle }} &bull; {{ $post->created_at->diffForHumans() }}</div>
                    </div>
                </a>
            </div>

            <!-- Média -->
            @if($post->media && $post->media->isVideo())
                <video src="{{ route('media.show', $post->media->id) }}" controls autoplay playsinline loop style="width: 100%; max-height: 480px; background: black;"></video>
            @elseif($post->media && $post->media->isAudio())
                <div style="padding: 24px; text-align: center; background: #161824;">
                    <div style="font-size: 40px; margin-bottom: 10px;">🎵</div>
                    <audio src="{{ route('media.show', $post->media->id) }}" controls style="width: 100%;"></audio>
                </div>
            @elseif($post->media && $post->media->isImage())
                <img src="{{ route('media.show', $post->media->id) }}" style="width: 100%; max-height: 500px; object-fit: contain; background: black;">
            @endif

            @if($post->body)
                <div style="padding: 16px; font-size: 15px; line-height: 1.5; color: #f1f5f9; white-space: pre-wrap;">
                    {!! nl2br(e($post->body)) !!}
                </div>
            @endif
        </div>

        <!-- Section Commentaires -->
        <h2 style="font-size: 16px; font-weight: 700; margin-bottom: 16px;">Commentaires ({{ $post->comments->count() }})</h2>

        @if($post->comments_allowed)
            <form method="POST" action="{{ route('comments.store', $post->id) }}" style="margin-bottom: 24px;">
                @csrf
                <div style="display: flex; gap: 8px;">
                    <input type="text" name="body" placeholder="Votre commentaire..." required maxlength="1000" class="creator-input" style="border-radius: var(--radius-full);">
                    <button type="submit" class="btn-primary-gradient" style="width: auto; padding: 0 20px; border-radius: var(--radius-full);">Envoyer</button>
                </div>
            </form>
        @else
            <div style="padding: 12px; background: rgba(255,255,255,0.05); border-radius: var(--radius-md); text-align: center; font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
                Les commentaires sont fermés pour ce post.
            </div>
        @endif

        <div style="display: flex; flex-direction: column; gap: 14px;">
            @forelse($post->comments as $comment)
                <div style="display: flex; gap: 10px; font-size: 14px;">
                    <div class="author-chip-avatar" style="width: 28px; height: 28px; font-size: 11px; flex-shrink: 0;">
                        {{ mb_strtoupper(mb_substr($comment->user->profile->handle, 0, 1)) }}
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: baseline; gap: 6px;">
                            <strong style="color: white; font-size: 13px;">{{ $comment->user->profile->effective_name }}</strong>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <div style="color: #cbd5e1; margin-top: 2px;">
                            {!! nl2br(e($comment->body)) !!}
                        </div>
                    </div>
                </div>
            @empty
                <div style="color: var(--text-muted); font-size: 13px; text-align: center; padding: 20px 0;">Aucun commentaire.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
