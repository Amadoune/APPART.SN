<x-layouts.public
    title="APPART.SN — Immobilier à vendre et à louer au Sénégal"
    description="Trouvez des annonces immobilières publiques à vendre et à louer à Dakar et au Sénégal sur APPART.SN."
    :canonical="route('home')"
>
    <section class="hero">
        <div class="shell-container hero__grid">
            <div>
                <p class="shell-kicker">L'immobilier, autrement</p>
                <h1>Trouvez votre <em>place.</em></h1>
                <p class="hero__copy">Des espaces à vivre, travailler et investir. Une expérience claire et rassurante, conçue pour le marché immobilier sénégalais.</p>
                <div class="hero__trust"><span>Annonces qualifiées</span><span>Expérience locale</span><span>Accompagnement clair</span></div>
            </div>
            <div class="hero-visual" role="img" aria-label="Illustration architecturale contemporaine">
                <div class="hero-card"><div><small>À découvrir</small><strong>Vivre face à l'océan</strong></div><span class="hero-card__badge">Dakar</span></div>
            </div>
        </div>
        <div class="shell-container">
            <form class="search-panel" id="recherche" method="GET" action="{{ route('public-search.experience') }}" aria-label="Rechercher un bien immobilier">
                <div class="search-panel__fields">
                    <div class="search-field"><label for="intent">Projet</label><select id="intent" name="transaction" required><option value="sale">Acheter</option><option value="rent">Louer</option></select></div>
                    <div class="search-field"><label for="location">Ville</label><input id="location" name="city" type="text" value="Dakar" maxlength="80" required autocomplete="address-level2"></div>
                    <div class="search-field"><label for="property">Type de bien</label><select id="property" name="propertyType" required><option value="apartment">Appartement</option><option value="villa">Villa</option><option value="land">Terrain</option></select></div>
                    <button class="shell-button shell-button--primary" type="submit">Rechercher <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </div>
    </section>

    <section class="shell-section" id="biens">
        <div class="shell-container">
            <p class="shell-kicker">Selon votre projet</p>
            <h2 class="shell-heading">Un bien pour chaque manière d'habiter.</h2>
            <div class="category-grid">
                <button class="category-card" type="button" data-shell-action><span class="category-card__icon">⌂</span><h3>Appartements</h3><p>Du studio urbain au grand appartement familial.</p></button>
                <button class="category-card" type="button" data-shell-action><span class="category-card__icon">◇</span><h3>Villas</h3><p>Des maisons pour respirer, recevoir et grandir.</p></button>
                <button class="category-card" type="button" data-shell-action><span class="category-card__icon">□</span><h3>Terrains</h3><p>Le point de départ de vos projets de demain.</p></button>
                <button class="category-card" type="button" data-shell-action><span class="category-card__icon">↗</span><h3>Professionnel</h3><p>Bureaux et commerces au rythme de votre activité.</p></button>
            </div>
        </div>
    </section>

    <section class="shell-section listings" id="quartiers">
        <div class="shell-container">
            <div class="section-row"><div><p class="shell-kicker">Sélection du moment</p><h2 class="shell-heading">Des lieux qui donnent envie de se projeter.</h2></div><button class="shell-button shell-button--quiet" type="button" data-shell-action>Voir toutes les annonces</button></div>
            <div class="listing-grid">
                @forelse ($publicListings ?? [] as $listing)
                    <article class="listing-card">
                        <a href="{{ url($listing->canonicalPath) }}" aria-label="Ouvrir {{ $listing->headline ?? 'l’annonce' }}">
                            <div class="listing-card__visual" @if ($listing->primaryImageUrl !== null) style="background-image:url('{{ $listing->primaryImageUrl }}');background-size:cover;background-position:center" @endif><span class="listing-card__tag">Annonce publiée</span></div>
                            <div class="listing-card__body"><p class="listing-card__meta">Dakar · Donnée réelle PostgreSQL</p><h3>{{ $listing->headline ?? 'Annonce immobilière' }}</h3><p class="listing-card__meta">{{ ucfirst($listing->propertyType) }}</p><p class="listing-card__price">Voir l’annonce</p></div>
                        </a>
                    </article>
                @empty
                    <p>Aucune annonce publique disponible pour le moment.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="shell-section" id="professionnels"><div class="shell-container"><div class="pro-panel"><div class="pro-panel__content"><p class="shell-kicker">Propriétaires & professionnels</p><h2>Mettez votre bien en lumière.</h2><p>Une future expérience de publication sobre et guidée, pensée pour présenter chaque adresse avec exigence.</p><button class="shell-button shell-button--primary" type="button" data-shell-action>Découvrir l'espace annonceur</button></div><div class="pro-panel__visual" role="img" aria-label="Illustration d'une résidence moderne"></div></div></div></section>

    <section class="shell-section" id="confiance"><div class="shell-container"><p class="shell-kicker">La confiance comme fondation</p><h2 class="shell-heading">Moins de bruit. Plus de clarté.</h2><div class="trust-grid"><div class="trust-item"><strong>Une lecture simple</strong><p>Des informations organisées pour comprendre rapidement l'essentiel d'un bien.</p></div><div class="trust-item"><strong>Une approche locale</strong><p>Des codes et des usages pensés à partir des réalités du marché sénégalais.</p></div><div class="trust-item"><strong>Une plateforme durable</strong><p>Une base visuelle accessible, performante et prête à évoluer par étapes.</p></div></div></div></section>
</x-layouts.public>
