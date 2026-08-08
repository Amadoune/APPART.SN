# Notifications Runtime — Availability Policy

| Résultat owner source | Disponibilité Runtime |
|---|---|
| Found, Empty ou Missing | `Available` |
| Corrupted | `Corrupted` |
| DependencyUnavailable | `DependencyUnavailable` |
| Exception technique | `DependencyUnavailable` |

`DependencyUnavailable` est prioritaire sur `Corrupted`. La politique est exhaustive, mécanique et fail-closed.
