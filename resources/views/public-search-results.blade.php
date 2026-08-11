@php
    $searchType = match ($query->propertyType) {
        'apartment' => 'Appartements',
        'villa' => 'Villas',
        'land' => 'Terrains',
        default => 'Biens immobiliers',
    };
    $searchIntent = $query->transaction === 'rent' ? 'à louer' : 'à acheter';
    $searchLocation = $query->city !== null ? ' à '.$query->city : '';
    $searchTitle = $searchType.' '.$searchIntent.$searchLocation.' — APPART.SN';
    $searchDescription = 'Découvrez les '.$searchType.' '.$searchIntent.$searchLocation.' parmi les annonces publiques disponibles sur APPART.SN.';
    $searchCanonical = route('public-search.experience', array_filter([
        'transaction' => $query->transaction,
        'city' => $query->city,
        'propertyType' => $query->propertyType,
        'after' => $query->afterCanonicalPath,
    ]));
@endphp
<x-layouts.public :title="$searchTitle" :description="$searchDescription" :canonical="$searchCanonical">
    <section class="search-results-hero">
        <div class="shell-container">
            <p class="shell-kicker">Recherche immobilière</p>
            <h1>Trouvez un lieu qui vous ressemble.</h1>
            <p>Affinez vos critères. Les résultats proviennent exclusivement des annonces publiques disponibles.</p>

            <form class="search-panel search-panel--results" method="GET" action="{{ route('public-search.experience') }}" aria-label="Rechercher un bien immobilier">
                <div class="search-panel__fields">
                    <div class="search-field">
                        <label for="results-transaction">Projet</label>
                        <select id="results-transaction" name="transaction" required>
                            <option value="sale" @selected($query->transaction === 'sale')>Acheter</option>
                            <option value="rent" @selected($query->transaction === 'rent')>Louer</option>
                        </select>
                    </div>
                    <div class="search-field">
                        <label for="results-city">Ville</label>
                        <input id="results-city" name="city" value="{{ $query->city }}" maxlength="80" required autocomplete="address-level2">
                    </div>
                    <div class="search-field">
                        <label for="results-property-type">Type de bien</label>
                        <select id="results-property-type" name="propertyType" required>
                            <option value="apartment" @selected($query->propertyType === 'apartment')>Appartement</option>
                            <option value="villa" @selected($query->propertyType === 'villa')>Villa</option>
                            <option value="land" @selected($query->propertyType === 'land')>Terrain</option>
                        </select>
                    </div>
                    <button class="shell-button shell-button--primary" type="submit">Rechercher <span aria-hidden="true">→</span></button>
                </div>
            </form>
        </div>
    </section>

    <section class="shell-section search-results" aria-labelledby="search-results-title">
        <div class="shell-container">
            <div class="search-results__heading">
                <div>
                    <p class="shell-kicker">{{ $query->transaction === 'rent' ? 'À louer' : 'À acheter' }}</p>
                    <h2 id="search-results-title" class="shell-heading">
                        {{ $query->propertyType === 'apartment' ? 'Appartements' : ($query->propertyType === 'villa' ? 'Villas' : 'Terrains') }}
                        @if ($query->city !== null) à {{ $query->city }} @endif
                    </h2>
                </div>
                @if ($result->status === \App\Application\PublicSearchResults\PublicSearchResultsStatus::Available)
                    <p class="search-results__count">{{ count($result->items) }} {{ count($result->items) > 1 ? 'biens affichés' : 'bien affiché' }}</p>
                @endif
            </div>

            @if ($result->status === \App\Application\PublicSearchResults\PublicSearchResultsStatus::Available)
                <div class="listing-grid listing-grid--results">
                    @foreach ($result->items as $listing)
                        <article class="listing-card listing-card--result">
                            <a href="{{ url($listing->canonicalPath) }}" aria-label="Ouvrir l’annonce {{ $listing->headline ?? 'immobilière' }}">
                                <div class="listing-card__visual" @if ($listing->primaryImageUrl !== null) style="background-image:url('{{ $listing->primaryImageUrl }}');background-size:cover;background-position:center" @endif>
                                    <span class="listing-card__tag">{{ $listing->transaction === 'rent' ? 'À louer' : 'À acheter' }}</span>
                                </div>
                                <div class="listing-card__body">
                                    <p class="listing-card__meta">{{ $listing->city ?? 'Localisation non communiquée' }}</p>
                                    <h3>{{ $listing->headline ?? 'Annonce immobilière' }}</h3>
                                    <p class="listing-card__meta">{{ $listing->propertyType === 'apartment' ? 'Appartement' : ucfirst($listing->propertyType) }}</p>
                                    @if ($listing->surfaceSquareMeters !== null || $listing->roomCount !== null)
                                        <p class="listing-card__facts">
                                            @if ($listing->surfaceSquareMeters !== null) {{ $listing->surfaceSquareMeters }} m² @endif
                                            @if ($listing->surfaceSquareMeters !== null && $listing->roomCount !== null) <span aria-hidden="true">·</span> @endif
                                            @if ($listing->roomCount !== null) {{ $listing->roomCount }} pièces @endif
                                        </p>
                                    @endif
                                    <p class="listing-card__price">Prix non communiqué</p>
                                    <span class="listing-card__link">Voir l’annonce <span aria-hidden="true">→</span></span>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

                @if ($result->nextCursor !== null)
                    <div class="search-results__more">
                        <a class="shell-button shell-button--quiet" href="{{ route('public-search.experience', array_filter(['transaction' => $query->transaction, 'city' => $query->city, 'propertyType' => $query->propertyType, 'after' => $result->nextCursor])) }}">Voir plus de résultats</a>
                    </div>
                @endif
            @elseif ($result->status === \App\Application\PublicSearchResults\PublicSearchResultsStatus::Empty)
                <div class="search-state" role="status">
                    <span class="search-state__icon" aria-hidden="true">⌂</span>
                    <h2>Aucun bien ne correspond actuellement à votre recherche.</h2>
                    <p>Essayez une autre ville ou modifiez vos critères.</p>
                    <a class="shell-button shell-button--primary" href="{{ route('home') }}#recherche">Modifier les critères</a>
                </div>
            @else
                <div class="search-state search-state--error" role="alert">
                    <h2>La recherche est momentanément indisponible.</h2>
                    <p>Veuillez réessayer dans quelques instants. Aucun détail technique n’est exposé.</p>
                    <a class="shell-button shell-button--quiet" href="{{ route('public-search.experience', ['transaction' => $query->transaction, 'city' => $query->city, 'propertyType' => $query->propertyType]) }}">Réessayer</a>
                </div>
            @endif
        </div>
    </section>
</x-layouts.public>
