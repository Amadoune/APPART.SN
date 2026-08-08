# Delivery Matrix — Legacy Migration

| Stream | Source exclusive | Propagations homonymes |
|---|---|---|
| Inventory | `LegacyMigrationInventoryEventV1` | Available, Missing, Corrupted, DependencyUnavailable |
| Wave | `LegacyMigrationWaveEventV1` | Ready, Blocked, Completed, Missing, Corrupted, DependencyUnavailable |
| Reconciliation | `LegacyMigrationReconciliationEventV1` | Matched, Divergent, Pending, Missing, Corrupted, DependencyUnavailable |
| Quarantine | `LegacyMigrationQuarantineEventV1` | Empty, ContainsItems, Missing, Corrupted, DependencyUnavailable |
| Cutover | `LegacyMigrationCutoverEventV1` | Ready, Blocked, Completed, Missing, Corrupted, DependencyUnavailable |

Les 27 propagations sont exhaustives, strictement mécaniques, bijectives et homonymes. Elles n'introduisent aucune réduction, interprétation, agrégation ou valeur de fallback.

Le type Event reste une propriété de la Delivery V1. Le Payload canonique reste exactement `{status, observedAt}`.
