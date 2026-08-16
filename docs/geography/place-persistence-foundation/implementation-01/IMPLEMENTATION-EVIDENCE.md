# Implementation Evidence

## Repository

- `find` retourne null seulement pour root absent et échoue fermé sur snapshot corrompu.
- `add` persiste root et aliases atomiquement et traduit les collisions id/code vers les exceptions Domain existantes.
- `save` applique `WHERE aggregate_version = expectedVersion`, exige une version candidate supérieure et remplace les aliases dans la même transaction.
- une Place fusionnée reste relue par son ID historique, sans redirection.

## Boundaries

- `PlaceRegistry` n'est pas modifié ;
- aucune dépendance Public Projection, Property Authoring, Search ou F1 ;
- aucun événement/outbox ou lifecycle n'est redécidé ;
- Provider nominatif enregistré dans `bootstrap/providers.php`.

## Tests

Mapper : root complet, parent, coordonnées présentes/nulles, aliases, enabled, merged, version et corruption. PostgreSQL : add/find/save, parent, merge, collisions, locking et migration up/down/réapplication.
