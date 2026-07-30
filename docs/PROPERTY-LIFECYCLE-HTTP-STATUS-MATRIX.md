# Property Lifecycle HTTP Status Matrix

| Résultat applicatif | HTTP | `status` |
|---|---:|---|
| `Applied` | 200 | `applied` |
| `AlreadyApplied` | 200 | `already_applied` |
| `Denied` | 422 | `denied` |
| `ConcurrencyConflict` | 409 | `concurrency_conflict` |
| `PersistenceFailure` | 503 | `persistence_failure` |

La validation JSON invalide retourne 422 selon le contrat Laravel existant. Un UUID de route invalide ne correspond pas à la route et retourne 404. Le mapping applicatif est exhaustif et ne comporte aucune branche par défaut.
