# Historical Canonical Qualification Analysis

## Problème

Le Web ne peut pas construire librement `HistoricalCanonical`. L'absence d'une projection Current ne prouve pas qu'une canonical est Historical. Il faut une décision d'identité explicite, possédée par `ContentSeo`, avant toute résolution de redirection.

## Frontière retenue

`HistoricalCanonicalQualifier::qualify(CanonicalUrl)` reçoit une canonical publique déjà normalisée. Il retourne une qualification fermée : `Current`, `Historical`, `Unknown`, `Ambiguous` ou `Corrupted`.

Seule la fabrique `historical` expose un `HistoricalCanonical`. Les quatre autres résultats rendent cette propriété nulle. Le contrat ne reçoit ni chemin libre, ni `ListingId`, et ne retourne aucune destination.

## Séparation des responsabilités

La qualification répond uniquement à « quelle est la disposition de cette identité publique ? ». Elle ne consulte pas `PublicListingQuery`, ne résout pas une redirection, ne recalcule pas une canonical et ne lit aucun modèle métier.

## Hors périmètre 3.10CA

PostgreSQL, migration, mapper, Runtime, Laravel, HTTP, binding, redirection et toute modification des fondations 3.10A–C.
