# Administration Console HTTP Foundation — HTTP Matrix

| Surface | Résultat V1 | HTTP | Corps |
|---|---|---:|---|
| Operator | `Available` | 200 | `{"status":"available"}` |
| Operator | `Unavailable` | 200 | `{"status":"unavailable"}` |
| Operator | `Missing` | 404 | `{"status":"missing"}` |
| Operator | `Corrupted` | 503 | `{"status":"corrupted"}` |
| Operator | `DependencyUnavailable` | 503 | `{"status":"dependency_unavailable"}` |
| Queue | `Ready` | 200 | `{"status":"ready"}` |
| Queue | `Empty` | 200 | `{"status":"empty"}` |
| Queue | `Missing` | 404 | `{"status":"missing"}` |
| Queue | `Corrupted` | 503 | `{"status":"corrupted"}` |
| Queue | `DependencyUnavailable` | 503 | `{"status":"dependency_unavailable"}` |
| Audit | `Available` | 200 | `{"status":"available"}` |
| Audit | `Missing` | 404 | `{"status":"missing"}` |
| Audit | `Corrupted` | 503 | `{"status":"corrupted"}` |
| Audit | `DependencyUnavailable` | 503 | `{"status":"dependency_unavailable"}` |

Les mappings sont exhaustifs, sans branche `default`, fallback, agrégation ou décision supplémentaire.
