<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $listing->headline }}</title>
    <meta name="description" content="{{ $listing->description }}">
    <link rel="canonical" href="{{ $listing->canonicalUrl }}">
    <meta name="robots" content="{{ $listing->htmlRobotsDirective }}">
    @if ($listing->publicJsonLd !== null)
        <script type="application/ld+json">{!! $listing->publicJsonLd !!}</script>
    @endif
</head>
<body>
    <nav aria-label="Fil d'Ariane">
        <ol>
            @foreach ($listing->breadcrumb as $item)
                <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
            @endforeach
        </ol>
    </nav>

    <main>
        <h1>{{ $listing->headline }}</h1>
        <p>{{ $listing->description }}</p>

        @if ($listing->publicMediaUrl !== null)
            <img src="{{ $listing->publicMediaUrl }}" alt="{{ $listing->headline }}">
        @endif

        <dl>
            <dt>Type de bien</dt>
            <dd>{{ $listing->propertyType }}</dd>
            @if ($listing->surfaceSquareMeters !== null)
                <dt>Surface</dt>
                <dd>{{ $listing->surfaceSquareMeters }} m²</dd>
            @endif
            <dt>Nombre de pièces</dt>
            <dd>{{ $listing->roomCount }}</dd>
        </dl>
    </main>
</body>
</html>
