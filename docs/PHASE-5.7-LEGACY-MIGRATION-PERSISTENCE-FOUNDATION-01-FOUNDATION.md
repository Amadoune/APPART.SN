# Persistence Foundation — Legacy Migration & Reconciliation

## Statut

`PHASE-5.7-LEGACY-MIGRATION-PERSISTENCE-FOUNDATION-01` est **GO CERTIFIÉ — OUVERTE** et constitue l'unique Foundation 5.7 active.

## Périmètre matérialisé

- owner de coordination unique : `LegacyMigration`, sans pouvoir métier ;
- contrat owner-scoped : `LegacyMigrationOwnerSource` ;
- cinq Revision States, cinq Read Results et cinq Write Results ;
- mapper canonique `LegacyMigrationOwnerSourceMapper` ;
- repository `PostgreSqlLegacyMigrationOwnerSource` ;
- migration additive `084_legacy_migration_owner_source.sql` et rollback associé.

Les streams `Inventory`, `Wave`, `Reconciliation`, `Quarantine` et `Cutover` sont indépendants. Aucun Provider, Runtime, HTTP, Event, Delivery, Outbox, Transport, Routing ou Consumer n'est ouvert.

## Autorité

Cette Foundation persiste des observations de coordination. Elle ne décide ni des données métier, ni des transformations, ni du cutover : chaque owner cible conserve exclusivement son autorité.
