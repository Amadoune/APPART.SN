# Runtime Foundation — Legacy Migration & Reconciliation

## Statut

`PHASE-5.7-LEGACY-MIGRATION-RUNTIME-FOUNDATION-01` est **GO CERTIFIÉ — OUVERTE** et constitue l'unique Foundation et l'unique jalon 5.7 actifs.

## Périmètre

Le Runtime expose exclusivement la disponibilité technique de `LegacyMigrationOwnerSource` au travers de `LegacyMigrationRuntimeV1`. Il ne publie aucun état métier et n'ouvre aucun Runtime Read, Reader public, HTTP, Event, Delivery, Outbox, Transport, Routing ou Consumer.

## Composants

- `LegacyMigrationRuntimeV1` ;
- `LegacyMigrationRuntimeAvailability` ;
- `LegacyMigrationRuntimeDiagnostics` ;
- `LegacyMigrationRuntimeAvailabilityPolicy` ;
- `DeterministicLegacyMigrationRuntime` ;
- `DeterministicLegacyMigrationRuntimeAvailabilityPolicy` ;
- `LegacyMigrationRuntimeServiceProvider`.

La Persistence Foundation est GO CERTIFIÉ — FERMÉ — HISTORICAL_ONLY. Les capacités 5.1 à 5.6 restent finales, fermées et gelées.
