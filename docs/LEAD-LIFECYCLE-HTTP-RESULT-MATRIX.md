# Lead Lifecycle HTTP Result Matrix

| Résultat applicatif | HTTP |
|---|---:|
| `Applied` | 200 |
| `AlreadyApplied` | 200 |
| `Missing` | 404 |
| `VersionConflict` | 409 |
| `Denied` | 422 |
| `StateConflict` | 409 |
| `ContextDivergence` | 409 |
| `PersistenceCorrupted` | 503 |

La matrice est fermée, sans branche `default`. Seul `Denied` transporte le diagnostic métier reçu sans réinterprétation.
