# Event Matrix — Legacy Migration

| Stream | Type V1 | Réductions homonymes |
|---|---|---|
| Inventory | `legacy-migration.inventory.observed.v1` | Available, Missing, Corrupted, DependencyUnavailable |
| Wave | `legacy-migration.wave.observed.v1` | Ready, Blocked, Completed, Missing, Corrupted, DependencyUnavailable |
| Reconciliation | `legacy-migration.reconciliation.observed.v1` | Matched, Divergent, Pending, Missing, Corrupted, DependencyUnavailable |
| Quarantine | `legacy-migration.quarantine.observed.v1` | Empty, ContainsItems, Missing, Corrupted, DependencyUnavailable |
| Cutover | `legacy-migration.cutover.observed.v1` | Ready, Blocked, Completed, Missing, Corrupted, DependencyUnavailable |

Les 27 réductions sont exhaustives, mécaniques, bijectives et homonymes. La conversion utilise les catalogues fermés et ne comporte aucun fallback.

Tous les Payloads ont exactement la forme canonique `{status, observedAt}`. Aucun SubjectKey, Revision State, identifiant Legacy, PII ou donnée métier n'est propagé.
