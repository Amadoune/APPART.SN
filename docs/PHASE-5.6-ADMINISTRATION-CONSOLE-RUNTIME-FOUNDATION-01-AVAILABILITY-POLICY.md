# Availability Policy

| Résultat owner-local | Disponibilité Runtime |
|---|---|
| Found, Empty ou Missing | Available |
| Corrupted | Corrupted |
| DependencyUnavailable | DependencyUnavailable |
| exception technique | DependencyUnavailable |

`DependencyUnavailable` prévaut sur `Corrupted`. Aucun statut métier n'est propagé.
