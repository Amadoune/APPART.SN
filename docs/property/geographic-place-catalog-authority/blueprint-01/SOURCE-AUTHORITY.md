# Source Authority

## Source productive unique

La source retenue est `PlaceRegistry::find(PlaceId): ?Place`. `PlaceRegistry` étend le port read-only `PlaceLookup`; F0 le lie à `PostgreSqlPlaceRepository`, qui lit `geography.places` et reconstruit l’Aggregate `Place`.

La lecture expose :

- `null` pour un identifiant absent ;
- `Place::isEnabled()` ;
- `Place::mergedInto()` ;
- `Place::type()`.

Elle suffit donc à qualifier existence, activation, fusion et type sans SQL dans l’adaptateur.

## Intégrité

`PostgreSqlPlaceRepository::find` reconstruit le snapshot via `PlaceMapper`. Une donnée invalide est réduite en `PersistentPlaceIntegrity`; elle ne ressemble jamais à une absence. L’invariant Geography interdit en outre un snapshot reconstitué simultanément enabled et merged.

## Limite autoritative

Le type est observable, mais aucune règle inventoriée ne dit quels types sont adressables par RealEstateCatalog. `PlaceType::acceptsParent` régit uniquement la hiérarchie. F1 régit la sélection, pas l’adressabilité Property. La source factuelle est complète ; la policy d’interprétation adressable ne l’est pas.
