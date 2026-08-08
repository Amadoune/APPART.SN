# Dependency Matrix — Legacy Migration Owner Reader Boundary

| Composant futur | Dépendance autorisée | Dépendances interdites |
|---|---|---|
| Inventory Owner Reader | `LegacyMigrationOwnerSource`, contrat Inventory V1 | autres streams, Runtime, Infrastructure |
| Wave Owner Reader | `LegacyMigrationOwnerSource`, contrat Wave V1 | autres streams, Runtime, Infrastructure |
| Reconciliation Owner Reader | `LegacyMigrationOwnerSource`, contrat Reconciliation V1 | autres streams, Runtime, Infrastructure |
| Quarantine Owner Reader | `LegacyMigrationOwnerSource`, contrat Quarantine V1 | autres streams, Runtime, Infrastructure |
| Cutover Owner Reader | `LegacyMigrationOwnerSource`, contrat Cutover V1 | autres streams, Runtime, Infrastructure |

Sont également interdits : PostgreSQL, SQL, migration 084, Provider, binding, Runtime Read, HTTP, Event, Delivery, Outbox, Transport, Routing, Consumer et toute capacité gelée.
