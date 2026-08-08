# Availability Policy

| Résultat de lecture owner-scoped | Disponibilité Runtime |
|---|---|
| Found | `Available` |
| Missing | `Available` |
| Corrupted | `Corrupted` |
| DependencyUnavailable | `DependencyUnavailable` |
| Exception | `DependencyUnavailable` |

Les cinq streams sont sondés mécaniquement. `DependencyUnavailable` prévaut sur `Corrupted`; en l'absence de ces deux états, la source est `Available`. Aucun fallback ni état métier n'est produit.
