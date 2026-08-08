# Compatibility Matrix — Legacy Migration HTTP

| Surface HTTP | Dépendance exclusive | Dépendances interdites | Statut |
|---|---|---|---|
| Inventory | `LegacyMigrationInventoryReaderV1` | Owner Source, Runtime, Infrastructure | Compatible |
| Wave | `LegacyMigrationWaveReaderV1` | Owner Source, Runtime, Infrastructure | Compatible |
| Reconciliation | `LegacyMigrationReconciliationReaderV1` | Owner Source, Runtime, Infrastructure | Compatible |
| Quarantine | `LegacyMigrationQuarantineReaderV1` | Owner Source, Runtime, Infrastructure | Compatible |
| Cutover | `LegacyMigrationCutoverReaderV1` | Owner Source, Runtime, Infrastructure | Compatible |
| Provider | six singletons HTTP, cinq routes | Event, Delivery, Outbox | Compatible |

Discovery, Contracts, Persistence, Runtime, Boundary Audit et Owner Reader Foundation restent fermés. La migration 084 et les capacités 5.1 à 5.6 restent inchangées.
