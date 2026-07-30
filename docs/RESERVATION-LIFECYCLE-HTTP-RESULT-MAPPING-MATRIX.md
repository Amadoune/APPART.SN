# Reservation Lifecycle HTTP Result Mapping Matrix

| Résultat applicatif | HTTP | Diagnostic |
|---|---:|---|
| `Applied` | 200 | `null` |
| `AlreadyApplied` | 200 | `null` |
| `Missing` | 404 | `null` |
| `VersionConflict` | 409 | `null` |
| `StateConflict` | 409 | `null` |
| `Denied` | 422 | diagnostic workflow exact |
| `PersistenceCorrupted` | 503 | `null` |

Le mapping est exhaustif et ne possède aucune branche `default`.
