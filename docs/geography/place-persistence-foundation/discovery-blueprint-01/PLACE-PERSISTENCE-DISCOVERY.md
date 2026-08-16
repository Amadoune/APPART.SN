# Geography Place Persistence Foundation — Discovery / Blueprint 01

## Constat

Le Domain `Place`, `PlaceRegistry` et les use cases existent. Aucune implémentation persistante, table de snapshot courant ou binding de Registry n'existe. Les tables lifecycle/outbox/inbox et `public_geography` ne permettent pas la reconstruction autoritative.

## Cible

Une persistance PostgreSQL owner-local Geography matérialise l'Aggregate complet via `geography.places` et `geography.place_aliases`, exposée par `PostgreSqlPlaceRepository` et un mapper strict.

F1 lira la même source par un port query read-only dédié, sans étendre `PlaceRegistry`.

**Verdict : GO PROPOSÉ.**
