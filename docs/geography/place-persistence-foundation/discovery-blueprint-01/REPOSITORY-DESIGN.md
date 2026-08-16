# Repository Design

## Composants futurs

- `PostgreSqlPlaceRepository implements PlaceRegistry` ;
- `PlaceMapper` entre Aggregate et snapshots stricts ;
- `PlaceSnapshot`, `AliasSnapshot` ;
- `PlaceTransaction` et adapter PostgreSQL local ;
- exception d'intégrité persistence Geography si snapshot/driver invalide.

`find` lit root, parent type par join et aliases ordonnés, construit tous les Value Objects, puis appelle `Place::reconstitute`. Toute ligne partielle ou incohérente échoue fermée.

`add` et `save` suivent les sémantiques du contrat et ne déclenchent aucune décision Domain. Le Repository ne recherche pas par label, ne redirige pas les merges et n'expose pas SQL à Application.

Binding futur nominatif singleton/lazy : `PlaceRegistry → PostgreSqlPlaceRepository`, utilisant la connexion PostgreSQL applicative.
