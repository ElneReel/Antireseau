<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('insociable.name', 'Insociable') }} - {{ config('insociable.tagline') }}</title>
    <meta name="description" content="Un réseau social sans algorithme, sans followers, sans recommandations. Un fil commun chronologique strict.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="site-container site-header-inner">
            <a href="{{ auth()->check() ? route('feed') : route('home') }}" class="brand-logo" aria-label="Page d'accueil d'Insociable">
                Insociable<span>.</span>
            </a>

            <nav class="header-nav" aria-label="Navigation principale">
                @auth
                    <a href="{{ route('feed') }}" class="nav-link {{ request()->routeIs('feed') ? 'active' : '' }}">Fil</a>
                    <a href="{{ route('bookmarks.index') }}" class="nav-link {{ request()->routeIs('bookmarks.*') ? 'active' : '' }}">Sauvegardes</a>
                    <a href="{{ route('profiles.show', auth()->user()->profile->handle) }}" class="nav-link {{ request()->is('membres/' . auth()->user()->profile->handle) ? 'active' : '' }}">
                        {{ auth()->user()->profile->effective_name }}
                    </a>
                    <a href="{{ route('settings.edit') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" aria-label="Réglages du compte">Réglages</a>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.index') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" style="color: var(--accent); font-weight: 600;">Modération</a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" style="margin-left: 6px;">Déconnexion</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Connexion</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Rejoindre</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main-content" style="padding-top: 32px;">
        <div class="site-container">
            @if(session('success'))
                <div class="alert alert-success" role="status">
                    <span aria-hidden="true">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info" role="status">
                    <span aria-hidden="true">ℹ</span>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger" role="alert">
                    <span aria-hidden="true">⚠</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
        </div>

        {{ $slot }}
    </main>

    @auth
    <!-- Barre de navigation mobile compacte -->
    <nav class="mobile-nav" aria-label="Navigation mobile">
        <a href="{{ route('feed') }}" class="mobile-nav-item {{ request()->routeIs('feed') ? 'active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
            <span>Fil</span>
        </a>
        <a href="{{ route('feed') }}#publier" class="mobile-nav-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
            <span>Publier</span>
        </a>
        <a href="{{ route('bookmarks.index') }}" class="mobile-nav-item {{ request()->routeIs('bookmarks.*') ? 'active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
            <span>Sauvegardes</span>
        </a>
        <a href="{{ route('profiles.show', auth()->user()->profile->handle) }}" class="mobile-nav-item {{ request()->is('membres/' . auth()->user()->profile->handle) ? 'active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <span>Profil</span>
        </a>
        <a href="{{ route('settings.edit') }}" class="mobile-nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            <span>Réglages</span>
        </a>
    </nav>
    @endauth

    <footer class="site-footer">
        <div class="site-container site-footer-inner">
            <div>
                <strong>Insociable</strong> : {{ config('insociable.tagline') }}
            </div>
            <ul class="footer-links">
                <li><a href="{{ route('rules') }}">Règles du lieu</a></li>
                <li><a href="{{ route('privacy') }}">Confidentialité</a></li>
                <li><a href="{{ route('contact') }}">Contact</a></li>
            </ul>
        </div>
    </footer>
</body>
</html>
