# Outbox Foundation — Legacy Migration & Reconciliation

## Statut

`PHASE-5.7-LEGACY-MIGRATION-OUTBOX-FOUNDATION-01` est **GO CERTIFIÉ — OUVERTE** et constitue l'unique Foundation et l'unique jalon 5.7 actifs.

## Composants

Application : `LegacyMigrationOutboxWriter`, `LegacyMigrationOutboxReader`, `LegacyMigrationOutboxPolicy`, `LegacyMigrationOutboxResult` et `LegacyMigrationOutboxStatus`.

Infrastructure : `PostgreSqlLegacyMigrationOutboxRepository`, migration additive `085_legacy_migration_outbox.sql` et rollback associé.

Les seules sources sont les cinq Deliveries V1 certifiées. Chaque Delivery produit exactement un message owner-scoped append-only. Aucun Provider, binding, Runtime, HTTP, Reader, Event, Transport, Routing ou Consumer n'est créé.
