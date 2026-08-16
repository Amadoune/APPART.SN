# Migration Report

## Identité

Prochain numéro libre vérifié dans le worktree : **098**.

- Up : `098_geography_places.sql`.
- Down : `098_geography_places.down.sql`.

## Contenu

- schéma Geography garanti ;
- table root et aliases créées sans INSERT ni backfill ;
- PK/FK, `(country_code, code)` unique, checks version/self-reference/coordonnées/merge ;
- index de future sélection conforme au Blueprint F1 ;
- aucune table lifecycle, outbox/inbox ou `public_geography` altérée.

## Preuve PostgreSQL

Migration up, down et réapplication exécutées sur PostgreSQL 18. Les tables historiques Geography et Public Geography restent présentes après rollback. La migration est idempotente à l'application et le rollback est dédié aux deux nouvelles tables.
