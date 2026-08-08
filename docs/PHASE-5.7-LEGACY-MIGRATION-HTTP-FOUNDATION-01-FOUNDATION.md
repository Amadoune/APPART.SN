# HTTP Foundation — Legacy Migration & Reconciliation

## Statut

`PHASE-5.7-LEGACY-MIGRATION-HTTP-FOUNDATION-01` est **GO CERTIFIÉ — OUVERTE** et constitue l'unique Foundation et l'unique jalon 5.7 actifs.

## Surface créée

- cinq Controllers : Inventory, Wave, Reconciliation, Quarantine et Cutover ;
- cinq Form Requests strictes ;
- `LegacyMigrationResponseFactory` ;
- marqueur `LegacyMigrationHttpRuntimeV1` ;
- `LegacyMigrationHttpServiceProvider` ;
- cinq routes publiques GET V1 sous `/api/legacy-migration`.

Chaque Controller dépend exclusivement du Reader V1 correspondant. Les réponses contiennent seulement `status` et `observedAt`, avec `Cache-Control: no-store` et `X-Content-Type-Options: nosniff`.

Aucun accès direct à l'Owner Source, au Runtime, à PostgreSQL, au mapper ou à l'Infrastructure n'est introduit.
