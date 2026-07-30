# Phase 4.7 — Administrative Action Lifecycle HTTP Result Matrix

| Résultat applicatif | HTTP |
|---|---:|
| `Applied` | 200 |
| `AlreadyApplied` | 200 |
| `Missing` | 404 |
| `VersionConflict` | 409 |
| `Denied` | 422 |
| `StateConflict` | 409 |
| `ContextDivergence` | 409 |
| `TransitionDivergence` | 409 |
| `PersistenceCorrupted` | 503 |

La matrice est exhaustive, sans branche `default`. La couche HTTP ne transforme
pas les diagnostics associés à `Denied`.
