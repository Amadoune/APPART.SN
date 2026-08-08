# Legacy Migration & Reconciliation Contracts V1 — Result Matrix

| Surface | Catalogue fermé | Propriétés du Result |
|---|---|---|
| Inventory | `Available`, `Missing`, `Corrupted`, `DependencyUnavailable` | `status`, `observedAt` |
| Wave | `Ready`, `Blocked`, `Completed`, `Missing`, `Corrupted`, `DependencyUnavailable` | `status`, `observedAt` |
| Reconciliation | `Matched`, `Divergent`, `Pending`, `Missing`, `Corrupted`, `DependencyUnavailable` | `status`, `observedAt` |
| Quarantine | `Empty`, `ContainsItems`, `Missing`, `Corrupted`, `DependencyUnavailable` | `status`, `observedAt` |
| Cutover | `Ready`, `Blocked`, `Completed`, `Missing`, `Corrupted`, `DependencyUnavailable` | `status`, `observedAt` |

Les cinq catalogues totalisent 27 états explicitement fermés. Aucun fallback, payload extensible ou métadonnée libre n'est prévu.
