<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $listing->headline }}</title>
    @if ($listing->description !== null)<meta name="description" content="{{ $listing->description }}">@endif
    <link rel="canonical" href="{{ $listing->canonicalUrl }}">
    <meta name="robots" content="{{ $listing->htmlRobotsDirective }}">
    <meta property="og:locale" content="fr_SN">
    <meta property="og:site_name" content="APPART.SN">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $listing->headline ?? 'Annonce immobilière — APPART.SN' }}">
    @if ($listing->description !== null)<meta property="og:description" content="{{ $listing->description }}">@endif
    <meta property="og:url" content="{{ $listing->canonicalUrl }}">
    @if ($listing->publicMediaUrl !== null)<meta property="og:image" content="{{ $listing->publicMediaUrl }}">@endif
    <meta name="twitter:card" content="{{ $listing->publicMediaUrl !== null ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $listing->headline ?? 'Annonce immobilière — APPART.SN' }}">
    @if ($listing->description !== null)<meta name="twitter:description" content="{{ $listing->description }}">@endif
    @if ($listing->publicMediaUrl !== null)<meta name="twitter:image" content="{{ $listing->publicMediaUrl }}">@endif
    @if ($listing->publicJsonLd !== null)
        <script type="application/ld+json">{!! $listing->publicJsonLd !!}</script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.public-header')

    <main class="property-page">
        <div class="shell-container">
            <nav class="property-breadcrumb" aria-label="Fil d'Ariane">
                <ol>
                    @foreach ($listing->breadcrumb as $item)
                        <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                    @endforeach
                </ol>
            </nav>

            <section class="property-hero" aria-labelledby="property-title">
                <div class="property-hero__copy">
                    <div class="property-badges">
                        @if ($listing->transactionKind !== null)
                            <span class="property-badge property-badge--accent">{{ $listing->transactionKind === 'rent' ? 'À louer' : 'À vendre' }}</span>
                        @endif
                        <span class="property-badge">{{ $listing->propertyType === 'apartment' ? 'Appartement' : ucfirst($listing->propertyType) }}</span>
                    </div>
                    <h1 id="property-title">{{ $listing->headline ?? 'Annonce immobilière' }}</h1>
                    @if ($listing->city !== null)
                        <p class="property-location"><span aria-hidden="true">⌖</span> {{ $listing->city }}</p>
                    @endif
                    <p class="property-price">Prix non communiqué</p>
                    <p class="property-published">Publié le {{ $listing->publishedAt->format('d/m/Y') }}</p>
                </div>

                <aside class="property-contact" aria-label="Poursuivre avec cette annonce">
                    <p>Cette annonce vous intéresse ?</p>
                    <button class="shell-button shell-button--primary" type="button" disabled aria-describedby="contact-availability">Contacter</button>
                    <span id="contact-availability">Bientôt disponible</span>
                </aside>
            </section>

            @php
                $publicMediaUrl = $listing->publicMediaUrl;

                if ($publicMediaUrl !== null && parse_url($publicMediaUrl, PHP_URL_HOST) === request()->getHost()) {
                    $publicMediaUrl = request()->getSchemeAndHttpHost().parse_url($publicMediaUrl, PHP_URL_PATH);
                }
            @endphp
            @if ($publicMediaUrl !== null)
                <section class="property-gallery" aria-labelledby="gallery-title">
                    <h2 id="gallery-title" class="visually-hidden">Galerie photo</h2>
                    <figure class="property-gallery__main">
                        <img src="{{ $publicMediaUrl }}" alt="Vue principale — {{ $listing->headline ?? 'annonce immobilière' }}">
                    </figure>
                </section>
            @endif

            <div class="property-content">
                <div>
                    <section class="property-section" aria-labelledby="essentials-title">
                        <p class="shell-kicker">L'essentiel</p>
                        <h2 id="essentials-title">Le bien en un regard.</h2>
                        <dl class="property-facts">
                            @if ($listing->transactionKind !== null)
                                <div><dt>Transaction</dt><dd>{{ $listing->transactionKind === 'rent' ? 'Location' : 'Vente' }}</dd></div>
                            @endif
                            <div><dt>Type</dt><dd>{{ $listing->propertyType === 'apartment' ? 'Appartement' : ucfirst($listing->propertyType) }}</dd></div>
                            @if ($listing->surfaceSquareMeters !== null)
                                <div><dt>Surface</dt><dd>{{ $listing->surfaceSquareMeters }} m²</dd></div>
                            @endif
                            @if ($listing->roomCount > 0)
                                <div><dt>Pièces</dt><dd>{{ $listing->roomCount }}</dd></div>
                            @endif
                            @if ($listing->city !== null)
                                <div><dt>Ville</dt><dd>{{ $listing->city }}</dd></div>
                            @endif
                        </dl>
                    </section>

                    @if ($listing->description !== null && $listing->description !== '')
                        <section class="property-section property-description" aria-labelledby="description-title">
                            <p class="shell-kicker">À propos de ce bien</p>
                            <h2 id="description-title">Une adresse à découvrir.</h2>
                            <p>{{ $listing->description }}</p>
                        </section>
                    @endif

                    @if ($listing->city !== null)
                        <section class="property-section property-place" aria-labelledby="location-title">
                            <p class="shell-kicker">Localisation</p>
                            <h2 id="location-title">{{ $listing->city }}</h2>
                            <p>La localisation publique disponible est volontairement limitée à la ville.</p>
                        </section>
                    @endif
                </div>

                <aside class="property-sidebar">
                    <p class="shell-kicker">Votre projet</p>
                    <h2>Envie d'en savoir plus ?</h2>
                    <p>La prise de contact sera proposée dans une prochaine étape produit.</p>
                    <button class="shell-button shell-button--primary" type="button" disabled>Contact bientôt disponible</button>
                </aside>
            </div>
        </div>
    </main>

    @include('partials.public-footer')

    <div class="shell-notice" data-shell-notice role="status" aria-live="polite" hidden>
        Cette fonction sera disponible dans un prochain sprint.
    </div>
</body>
</html>
