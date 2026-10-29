<x-app-layout>
    <x-slot:title>{{ $profile->effective_name }} (@&lrm;{{ $profile->handle }}) — antireseau</x-slot:title>

    <div style="flex: 1; overflow-y: auto; padding: 20px 16px 80px;" x-data="{ activeTab: 'posts', showAntiFollowInfo: false }">
        <!-- Carte Profil Anti-Influenceur -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-glass); border-radius: var(--radius-lg); padding: 22px 18px; margin-bottom: 20px; position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                <div style="width: 68px; height: 68px; border-radius: 50%; background: var(--accent-gradient); display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 800; color: white; flex-shrink: 0; box-shadow: 0 0 20px rgba(255,0,85,0.25);">
                    @if($profile->avatar_path)
                        <img src="{{ route('media.avatar', $targetUser->id) }}" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    @else
                        {{ mb_strtoupper(mb_substr($profile->handle, 0, 1)) }}
                    @endif
                </div>

                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <h1 style="font-size: 19px; font-weight: 800; color: white;">{{ $profile->effective_name }}</h1>
                        <span style="font-size: 10px; font-weight: 800; background: rgba(255, 0, 85, 0.14); color: #ff0055; border: 1px solid rgba(255, 0, 85, 0.3); padding: 2px 7px; border-radius: 999px;">
                            0 ABONNÉ
                        </span>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 10px;">@&lrm;{{ $profile->handle }}</div>

                    @if($isSelf)
                        <a href="{{ route('settings.edit') }}" class="icon-btn" style="width: 100%; border-radius: var(--radius-full); font-size: 12px; font-weight: 700; height: 32px; border: 1px solid var(--border-subtle);">
                            ⚙️ Modifier mon profil
                        </a>
                    @else
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <!-- Bouton Manifeste Anti-Follow -->
                            <button type="button" @click="showAntiFollowInfo = !showAntiFollowInfo"
                                    style="flex: 1; height: 32px; border-radius: var(--radius-full); background: rgba(255,255,255,0.05); border: 1px dashed rgba(255,255,255,0.2); color: #94a3b8; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span>🚫</span>
                                <span style="text-decoration: line-through;">Suivre</span>
                                <span style="color: #ff0055; font-weight: 800;">(Aboli)</span>
                            </button>

                            @if($isBlockedByViewer)
                                <form method="POST" action="{{ route('blocks.destroy', $targetUser->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn" style="border-radius: var(--radius-full); font-size: 11px; height: 32px; padding: 0 12px; width: auto;">
                                        Débloquer
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('blocks.store') }}" onsubmit="return confirm('Bloquer ce profil ?');">
                                    @csrf
                                    <input type="hidden" name="target_id" value="{{ $targetUser->id }}">
                                    <button type="submit" class="icon-btn" style="border-radius: var(--radius-full); font-size: 11px; height: 32px; color: var(--danger); padding: 0 12px; width: auto;" title="Bloquer cet utilisateur">
                                        Bloquer
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Explication Anti-Follow au clic -->
            <div x-show="showAntiFollowInfo" x-transition x-cloak
                 style="background: rgba(255, 0, 85, 0.1); border: 1px solid rgba(255, 0, 85, 0.3); border-radius: 10px; padding: 10px 12px; font-size: 12px; color: #fda4af; margin-bottom: 14px; line-height: 1.4;">
                <strong>Pourquoi on ne peut pas suivre @&lrm;{{ $profile->handle }} ?</strong><br>
                Sur <strong>antireseau</strong>, le concept d'abonné est mort. Personne n'est influenceur. Vous voyez ses créations, ce qu'il repartage et ce qu'il aime, à égalité absolue avec tout le monde.
            </div>

            @if($profile->bio)
                <div style="font-size: 14px; color: #cbd5e1; line-height: 1.5; margin-bottom: 14px; white-space: pre-wrap;">
                    {!! nl2br(e($profile->bio)) !!}
                </div>
            @endif

            <!-- Empreinte Créative (Au lieu du compteur de followers) -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; border-top: 1px solid var(--border-subtle); padding-top: 14px; text-align: center;">
                <div @click="activeTab = 'posts'" style="cursor: pointer; padding: 6px; border-radius: 8px; background: rgba(255,255,255,0.02);">
                    <div style="font-size: 16px; font-weight: 800; color: #ffffff;">{{ $posts->count() }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">Publications</div>
                </div>
                <div @click="activeTab = 'reposts'" style="cursor: pointer; padding: 6px; border-radius: 8px; background: rgba(16, 185, 129, 0.05);">
                    <div style="font-size: 16px; font-weight: 800; color: #34d399;">{{ $repostedPosts->count() }}</div>
                    <div style="font-size: 11px; color: #34d399;">Repartages 🔁</div>
                </div>
                <div @click="activeTab = 'likes'" style="cursor: pointer; padding: 6px; border-radius: 8px; background: rgba(255, 0, 85, 0.05);">
                    <div style="font-size: 16px; font-weight: 800; color: #ff0055;">{{ $likedPosts->count() }}</div>
                    <div style="font-size: 11px; color: #ff0055;">Likes ❤️</div>
                </div>
            </div>
        </div>

        <!-- Barre des 3 Onglets (Publications / Repartages / Likes) -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); background: #12141c; border: 1px solid var(--border-subtle); border-radius: 12px; padding: 4px; margin-bottom: 16px;">
            <button type="button" @click="activeTab = 'posts'"
                    :style="activeTab === 'posts' ? 'background: #1e2230; color: #ffffff; font-weight: 800;' : 'color: var(--text-muted); font-weight: 600;'"
                    style="padding: 9px 6px; border-radius: 8px; font-size: 12px; transition: all 0.2s;">
                📸 Créations ({{ $posts->count() }})
            </button>
            <button type="button" @click="activeTab = 'reposts'"
                    :style="activeTab === 'reposts' ? 'background: rgba(16, 185, 129, 0.18); color: #34d399; font-weight: 800;' : 'color: var(--text-muted); font-weight: 600;'"
                    style="padding: 9px 6px; border-radius: 8px; font-size: 12px; transition: all 0.2s;">
                🔁 Repartages ({{ $repostedPosts->count() }})
            </button>
            <button type="button" @click="activeTab = 'likes'"
                    :style="activeTab === 'likes' ? 'background: rgba(255, 0, 85, 0.18); color: #ff0055; font-weight: 800;' : 'color: var(--text-muted); font-weight: 600;'"
                    style="padding: 9px 6px; border-radius: 8px; font-size: 12px; transition: all 0.2s;">
                ❤️ Likes ({{ $likedPosts->count() }})
            </button>
        </div>

        <!-- ONGLET 1 : SES PUBLICATIONS -->
        <div x-show="activeTab === 'posts'">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
                @forelse($posts as $post)
                    <a href="{{ route('posts.show', $post->id) }}" style="aspect-ratio: 1; border-radius: var(--radius-sm); overflow: hidden; background: #161822; position: relative; display: flex; align-items: center; justify-content: center; text-align: center; border: 1px solid var(--border-subtle);">
                        @if($post->media && $post->media->isImage())
                            <img src="{{ route('media.show', $post->media->id) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @elseif($post->media && $post->media->isVideo())
                            <div style="position: absolute; top: 6px; right: 6px; background: rgba(0,0,0,0.6); border-radius: 4px; padding: 2px 5px; font-size: 10px; z-index: 2;">🎬</div>
                            <video src="{{ route('media.show', $post->media->id) }}" style="width: 100%; height: 100%; object-fit: cover;"></video>
                        @elseif($post->media && $post->media->isAudio())
                            <div style="font-size: 24px;">🎵</div>
                        @else
                            <div style="padding: 8px; font-size: 11px; color: #cbd5e1; line-height: 1.3; overflow: hidden; text-overflow: ellipsis;">
                                {{ Str::limit($post->body, 55) }}
                            </div>
                        @endif
                        <div style="position: absolute; bottom: 4px; left: 6px; font-size: 10px; color: white; text-shadow: 0 1px 3px rgba(0,0,0,0.9); display: flex; gap: 6px;">
                            <span>❤️ {{ $post->likes_count }}</span>
                            @if($post->reposts_count > 0)
                                <span>🔁 {{ $post->reposts_count }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div style="grid-column: span 3; text-align: center; color: var(--text-muted); padding: 40px 0; font-size: 13px;">
                        Aucune publication créée pour le moment.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ONGLET 2 : SES REPARTAGES (Ce qu'il a fait remonter) -->
        <div x-show="activeTab === 'reposts'" x-cloak>
            <div style="font-size: 12px; color: #34d399; margin-bottom: 10px; text-align: center;">
                🔁 Publications que <strong>@&lrm;{{ $profile->handle }}</strong> a propulsées en haut du fil commun
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
                @forelse($repostedPosts as $post)
                    <a href="{{ route('posts.show', $post->id) }}" style="aspect-ratio: 1; border-radius: var(--radius-sm); overflow: hidden; background: #161822; position: relative; display: flex; align-items: center; justify-content: center; text-align: center; border: 1px solid rgba(16, 185, 129, 0.35);">
                        @if($post->media && $post->media->isImage())
                            <img src="{{ route('media.show', $post->media->id) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @elseif($post->media && $post->media->isVideo())
                            <video src="{{ route('media.show', $post->media->id) }}" style="width: 100%; height: 100%; object-fit: cover;"></video>
                        @elseif($post->media && $post->media->isAudio())
                            <div style="font-size: 24px;">🎵</div>
                        @else
                            <div style="padding: 8px; font-size: 11px; color: #cbd5e1; line-height: 1.3;">
                                {{ Str::limit($post->body, 55) }}
                            </div>
                        @endif
                        <div style="position: absolute; top: 4px; left: 4px; background: rgba(16,185,129,0.85); color: #052e16; font-weight: 800; font-size: 9px; padding: 2px 5px; border-radius: 4px;">
                            @&lrm;{{ $post->user->profile->handle }}
                        </div>
                    </a>
                @empty
                    <div style="grid-column: span 3; text-align: center; color: var(--text-muted); padding: 40px 0; font-size: 13px;">
                        Aucun repartage pour le moment.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ONGLET 3 : SES LIKES -->
        <div x-show="activeTab === 'likes'" x-cloak>
            <div style="font-size: 12px; color: #ff0055; margin-bottom: 10px; text-align: center;">
                ❤️ Publications aimées par <strong>@&lrm;{{ $profile->handle }}</strong>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
                @forelse($likedPosts as $post)
                    <a href="{{ route('posts.show', $post->id) }}" style="aspect-ratio: 1; border-radius: var(--radius-sm); overflow: hidden; background: #161822; position: relative; display: flex; align-items: center; justify-content: center; text-align: center; border: 1px solid rgba(255, 0, 85, 0.3);">
                        @if($post->media && $post->media->isImage())
                            <img src="{{ route('media.show', $post->media->id) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @elseif($post->media && $post->media->isVideo())
                            <video src="{{ route('media.show', $post->media->id) }}" style="width: 100%; height: 100%; object-fit: cover;"></video>
                        @elseif($post->media && $post->media->isAudio())
                            <div style="font-size: 24px;">🎵</div>
                        @else
                            <div style="padding: 8px; font-size: 11px; color: #cbd5e1; line-height: 1.3;">
                                {{ Str::limit($post->body, 55) }}
                            </div>
                        @endif
                        <div style="position: absolute; bottom: 4px; left: 4px; background: rgba(0,0,0,0.7); color: #fff; font-size: 9px; padding: 2px 5px; border-radius: 4px;">
                            @&lrm;{{ $post->user->profile->handle }}
                        </div>
                    </a>
                @empty
                    <div style="grid-column: span 3; text-align: center; color: var(--text-muted); padding: 40px 0; font-size: 13px;">
                        Aucun like pour le moment.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
