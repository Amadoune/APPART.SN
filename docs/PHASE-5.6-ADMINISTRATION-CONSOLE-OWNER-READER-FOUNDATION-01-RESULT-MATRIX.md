# Administration Console Owner Reader Foundation — Result Matrix

| Reader | Résultat source | Status owner | Résultat V1 |
|---|---|---|---|
| Operator | `Available` | `Available` | `Available` |
| Operator | `Unavailable` | `Unavailable` | `Unavailable` |
| Operator | `Missing` | `Missing` | `Missing` |
| Operator | `Corrupted` | `Corrupted` | `Corrupted` |
| Operator | `DependencyUnavailable` | `DependencyUnavailable` | `DependencyUnavailable` |
| Queue | `Ready` | `Ready` | `Ready` |
| Queue | `Empty` | `Empty` | `Empty` |
| Queue | `Missing` | `Missing` | `Missing` |
| Queue | `Corrupted` | `Corrupted` | `Corrupted` |
| Queue | `DependencyUnavailable` | `DependencyUnavailable` | `DependencyUnavailable` |
| Audit | `Available` | `Available` | `Available` |
| Audit | `Missing` | `Missing` | `Missing` |
| Audit | `Corrupted` | `Corrupted` | `Corrupted` |
| Audit | `DependencyUnavailable` | `DependencyUnavailable` | `DependencyUnavailable` |

Les trois `match` sont exhaustifs et sans branche `default`. Les états du catalogue owner incompatibles avec un stream sont explicitement impossibles ; ils ne sont jamais convertis en fallback.
