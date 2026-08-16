# Geography Place Persistence Foundation Implementation 01

## Implémentation

La persistance autoritative de `Place` est matérialisée par :

- migration additive `098_geography_places.sql` et rollback dédié ;
- `geography.places` et `geography.place_aliases` ;
- `PostgreSqlPlaceRepository implements PlaceRegistry` ;
- `PlaceMapper`, snapshots stricts et corruption fail-closed ;
- `PostgreSqlPlaceTransaction` locale ;
- binding singleton `PlaceRegistry → PostgreSqlPlaceRepository`.

Le Repository reconstruit l'Aggregate complet, ses aliases, son parent, ses coordonnées, son état enabled/merged et sa version. Aucun port F1, HTTP, Projection ou Property Authoring n'est introduit.
