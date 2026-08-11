<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description }}">
    <title>{{ $title ?? 'APPART.SN' }}</title>
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <meta name="robots" content="{{ $robots }}">
    <meta property="og:locale" content="fr_SN">
    <meta property="og:site_name" content="APPART.SN">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $title ?? 'APPART.SN' }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    @if ($image !== null)<meta property="og:image" content="{{ $image }}">@endif
    <meta name="twitter:card" content="{{ $image !== null ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $title ?? 'APPART.SN' }}">
    <meta name="twitter:description" content="{{ $description }}">
    @if ($image !== null)<meta name="twitter:image" content="{{ $image }}">@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.public-header')

    <main>
        {{ $slot }}
    </main>

    @include('partials.public-footer')

    <div class="shell-notice" data-shell-notice role="status" aria-live="polite" hidden>
        Aperçu visuel uniquement — cette fonction sera connectée dans un prochain vertical slice.
    </div>
</body>
</html>
