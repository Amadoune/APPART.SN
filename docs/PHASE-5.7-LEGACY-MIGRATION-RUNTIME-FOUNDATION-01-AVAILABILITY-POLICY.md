# Availability Policy — Legacy Migration Runtime

Les cinq streams sont sondés avec une clé technique réservée et un instant UTC canonique. La réduction est fermée et déterministe.

| Résultat source | Disponibilité Runtime |
|---|---|
| Found | Available |
| Missing | Available |
| Corrupted | Corrupted |
| DependencyUnavailable | DependencyUnavailable |
| exception technique | DependencyUnavailable |

`DependencyUnavailable` prévaut sur `Corrupted` lorsque plusieurs streams répondent différemment. `Available` signifie seulement que la source est techniquement exploitable : ce statut ne signifie ni données présentes, ni quarantaine vide, ni vague prête, ni cutover prêt.
