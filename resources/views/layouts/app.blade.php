<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#090a0f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Insociable' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ createModalOpen: false }" @open-create-modal.window="createModalOpen = true">
    <div class="app-viewport">
        <!-- Barre haute (Top Bar) -->
        <header class="top-bar">
            <a href="{{ auth()->check() ? route('feed') : route('home') }}" class="brand-title">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="url(#logo-grad)" stroke-width="2.5"><defs><linearGradient id="logo-grad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#f43f5e"/><stop offset="50%" stop-color="#a855f7"/><stop offset="100%" stop-color="#6366f1"/></linearGradient></defs><circle cx="12" cy="12" r="10"></circle><path d="M8 12h8"></path></svg>
                <span>Insociable</span>
            </a>

            <div class="top-bar-actions">
                @auth
                    <!-- Bouton Actualiser le fil -->
                    <a href="{{ route('feed') }}" class="icon-btn" title="Actualiser le fil">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    </a>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.index') }}" class="icon-btn" style="color: var(--accent);" title="Modération">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="icon-btn" title="Déconnexion" style="color: var(--danger);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link" style="font-size: 14px; font-weight: 600;">Connexion</a>
                    <a href="{{ route('register') }}" class="btn-primary-gradient" style="font-size: 13px; padding: 6px 14px; width: auto;">Rejoindre</a>
                @endauth
            </div>
        </header>

        <!-- Messages Flash Toast -->
        @if(session('success'))
            <div style="position: absolute; top: 62px; left: 16px; right: 16px; background: rgba(16, 185, 129, 0.9); backdrop-filter: blur(10px); color: white; padding: 10px 16px; border-radius: var(--radius-md); z-index: 100; font-size: 13px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.5);"
                 x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
                <span>✓ {{ session('success') }}</span>
                <button type="button" @click="show = false" style="color: white; font-size: 18px;">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div style="position: absolute; top: 62px; left: 16px; right: 16px; background: rgba(239, 68, 68, 0.95); backdrop-filter: blur(10px); color: white; padding: 10px 16px; border-radius: var(--radius-md); z-index: 100; font-size: 13px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 20px rgba(0,0,0,0.5);"
                 x-data="{ show: true }" x-show="show">
                <span>⚠️ {{ session('error') }}</span>
                <button type="button" @click="show = false" style="color: white; font-size: 18px;">&times;</button>
            </div>
        @endif

        <!-- Contenu principal (Le Fil 1 post par écran ou pages) -->
        <main style="flex: 1; overflow: hidden; display: flex; flex-direction: column; position: relative;">
            {{ $slot }}
        </main>

        @auth
        <!-- Navigation Basse Dock Mobile Native (Instagram / TikTok Style) -->
        <nav class="bottom-dock">
            <!-- 1. Fil -->
            <a href="{{ route('feed') }}" class="dock-item {{ request()->routeIs('feed') ? 'active' : '' }}" title="Fil chronologique">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="{{ request()->routeIs('feed') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
            </a>

            <!-- 2. Sauvegardes -->
            <a href="{{ route('bookmarks.index') }}" class="dock-item {{ request()->routeIs('bookmarks.*') ? 'active' : '' }}" title="Mes sauvegardes">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="{{ request()->routeIs('bookmarks.*') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
            </a>

            <!-- 3. Bouton CRÉER (+) Central Lumineux -->
            <button type="button" @click="createModalOpen = true" class="create-btn-glow" title="Créer une publication">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            </button>

            <!-- 4. Réglages -->
            <a href="{{ route('settings.edit') }}" class="dock-item {{ request()->routeIs('settings.*') ? 'active' : '' }}" title="Réglages du compte">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            </a>

            <!-- 5. Profil -->
            <a href="{{ route('profiles.show', auth()->user()->profile->handle) }}" class="dock-item {{ request()->is('membres/' . auth()->user()->profile->handle) ? 'active' : '' }}" title="Mon profil">
                @if(auth()->user()->profile->avatar_path)
                    <img src="{{ route('media.avatar', auth()->id()) }}" alt="Avatar" style="width: 26px; height: 26px; border-radius: 50%; object-fit: cover; border: 2px solid {{ request()->is('membres/' . auth()->user()->profile->handle) ? 'var(--accent)' : 'transparent' }};">
                @else
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="{{ request()->is('membres/' . auth()->user()->profile->handle) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                @endif
            </a>
        </nav>

        <!-- Modal de Création Multi-Format (Photo, Vidéo, Audio, Texte) -->
        <div class="creator-modal-backdrop" x-show="createModalOpen" x-cloak @click.self="createModalOpen = false">
            <div class="creator-modal-card" x-data="{
                activeTab: 'photo',
                previewUrl: null,
                fileName: '',
                handleFile(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                        this.previewUrl = URL.createObjectURL(file);
                    }
                }
            }">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                    <h2 style="font-size: 18px; font-weight: 700;">Nouvelle Publication</h2>
                    <button type="button" @click="createModalOpen = false" style="color: var(--text-muted); font-size: 24px;">&times;</button>
                </div>

                <!-- Onglets de Format -->
                <div class="format-tabs">
                    <button type="button" class="format-tab-btn" :class="{ 'active': activeTab === 'photo' }" @click="activeTab = 'photo'">
                        <span style="font-size: 16px;">📸</span>
                        <span>Photo</span>
                    </button>
                    <button type="button" class="format-tab-btn" :class="{ 'active': activeTab === 'video' }" @click="activeTab = 'video'">
                        <span style="font-size: 16px;">🎬</span>
                        <span>Vidéo</span>
                    </button>
                    <button type="button" class="format-tab-btn" :class="{ 'active': activeTab === 'audio' }" @click="activeTab = 'audio'">
                        <span style="font-size: 16px;">🎙️</span>
                        <span>Audio</span>
                    </button>
                    <button type="button" class="format-tab-btn" :class="{ 'active': activeTab === 'text' }" @click="activeTab = 'text'">
                        <span style="font-size: 16px;">✍️</span>
                        <span>Texte</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ Str::random(32) }}">

                    <!-- Zone de fichier (selon onglet) -->
                    <div x-show="activeTab !== 'text'" style="margin-bottom: 16px;">
                        <label class="file-dropzone" style="display: block;">
                            <template x-if="!previewUrl">
                                <div>
                                    <div style="font-size: 32px; margin-bottom: 8px;" x-text="activeTab === 'photo' ? '🖼️' : (activeTab === 'video' ? '📹' : '🎵')"></div>
                                    <div style="font-size: 14px; font-weight: 600; color: #ffffff;" x-text="activeTab === 'photo' ? 'Choisir une photo (JPEG, PNG, WebP)' : (activeTab === 'video' ? 'Choisir une vidéo (MP4, WebM)' : 'Choisir un son (MP3, WAV, OGG)')"></div>
                                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Tapez pour parcourir vos fichiers</div>
                                </div>
                            </template>

                            <template x-if="previewUrl">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                    <template x-if="activeTab === 'photo'">
                                        <img :src="previewUrl" style="max-height: 180px; border-radius: var(--radius-sm); object-fit: contain;">
                                    </template>
                                    <template x-if="activeTab === 'video'">
                                        <video :src="previewUrl" controls style="max-height: 180px; border-radius: var(--radius-sm); width: 100%;"></video>
                                    </template>
                                    <template x-if="activeTab === 'audio'">
                                        <audio :src="previewUrl" controls style="width: 100%;"></audio>
                                    </template>
                                    <span style="font-size: 12px; color: var(--accent);" x-text="fileName"></span>
                                    <span style="font-size: 11px; color: var(--text-muted);">Cliquer pour changer de fichier</span>
                                </div>
                            </template>

                            <input type="file"
                                   name="media_file"
                                   style="display: none;"
                                   :accept="activeTab === 'photo' ? 'image/jpeg,image/png,image/webp' : (activeTab === 'video' ? 'video/mp4,video/webm' : 'audio/mpeg,audio/mp3,audio/wav,audio/ogg')"
                                   @change="handleFile($event)">
                        </label>
                    </div>

                    <!-- Légende ou Texte Principal -->
                    <div style="margin-bottom: 16px;">
                        <textarea name="body"
                                  class="creator-input"
                                  rows="4"
                                  maxlength="2000"
                                  :placeholder="activeTab === 'text' ? 'Écrivez votre pensée (elle apparaîtra en grand format)...' : 'Ajouter une légende...'"></textarea>
                    </div>

                    <!-- Options -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <label class="form-check" style="font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" name="comments_allowed" value="1" checked style="accent-color: var(--accent);">
                            <span>Autoriser les commentaires</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-primary-gradient">
                        <span>Publier maintenant</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </div>
</body>
</html>
