# APPART.TEST Public Search Results Read Model 01 — Boundary Audit

## Frontière retenue

La source de lecture unique est la projection publique certifiée `public_projection.listing_projections`, limitée à la génération active et aux enregistrements `current`.

Chaîne :

`PublicSearchResultsController` → `PublicSearchResultsReaderV1` → `PostgreSqlPublicSearchResultsReader` → `PostgreSqlPublicListingProjectionMapper` → `PublicListingReadModel`.

Le Controller et la Request ne connaissent ni PDO, ni SQL, ni Aggregate, ni OwnerSource. L'adapter Infrastructure réutilise le mapper certifié, qui vérifie le checksum avant toute exposition.

## Autorité et éligibilité

- aucune nouvelle source autoritative ;
- seules les projections de la génération active sont visibles ;
- seuls les records `current` sont retournés ;
- aucune lecture de Listing Lifecycle ou d'Aggregate ;
- la cohérence et l'indexabilité restent celles du pipeline de projection certifié ;
- toute corruption du payload ou du checksum ferme la lecture.

## Dépendances interdites confirmées

Domain, Aggregate, UseCase métier, Eloquent, SQL dans HTTP/Blade, migration et source Search parallèle restent absents.
