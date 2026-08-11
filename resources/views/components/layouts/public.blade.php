@props([
    'title' => null,
    'description' => 'APPART.SN — La nouvelle adresse de l’immobilier au Sénégal.',
    'canonical' => null,
    'robots' => 'index, follow',
    'ogType' => 'website',
    'image' => null,
])

@include('layouts.public', compact('slot', 'title', 'description', 'canonical', 'robots', 'ogType', 'image'))
