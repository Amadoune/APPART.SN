<header class="site-header">
    <div class="shell-container site-header__inner">
        <a class="brand" href="/" aria-label="APPART.SN — Accueil">
            <span class="brand__mark" aria-hidden="true">A</span>
            <span>APPART<span class="brand__dot">.</span>SN</span>
        </a>

        <nav class="desktop-nav" aria-label="Navigation principale">
            <a href="#biens">Acheter</a>
            <a href="#biens">Louer</a>
            <a href="#quartiers">Quartiers</a>
            <a href="#professionnels">Professionnels</a>
            <a href="{{ route('owner-dashboard') }}">Espace propriétaire</a>
        </nav>

        <div class="header-actions">
            <a class="shell-button shell-button--quiet" href="{{ route('iam-web-entry.login') }}">Se connecter</a>
            <a class="shell-button shell-button--primary" href="{{ route('iam-web-entry.login', ['next' => route('public-authoring.workspace', absolute: false)]) }}">Déposer une annonce</a>
            <button class="menu-toggle" type="button" aria-label="Ouvrir le menu" aria-expanded="false" data-menu-toggle>☰</button>
        </div>
    </div>
    <div class="shell-container mobile-menu" data-mobile-menu hidden>
        <nav aria-label="Navigation mobile">
            <a href="#biens">Acheter</a>
            <a href="#biens">Louer</a>
            <a href="#quartiers">Quartiers</a>
            <a href="#professionnels">Professionnels</a>
            <a href="{{ route('owner-dashboard') }}">Espace propriétaire</a>
            <a href="{{ route('iam-web-entry.login') }}">Se connecter</a>
        </nav>
    </div>
</header>
