# Media Item Lifecycle HTTP Result Matrix

| Résultat applicatif | HTTP |
|---|---|
| `Applied` | 200 |
| `AlreadyApplied` | 200 |
| `Missing` | 404 |
| `VersionConflict` | 409 |
| `StateConflict` | 409 |
| `ContextDivergence` | 409 |
| `Denied` | 422 |
| `PersistenceCorrupted` | 503 |

La réponse contient uniquement `status` et `diagnostic`. Aucune exception technique n'est exposée.
