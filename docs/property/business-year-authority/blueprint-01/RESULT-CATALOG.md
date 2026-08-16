# Result Catalog

Catalogue fermé minimal :

| Statut | Sémantique |
|---|---|
| `Resolved` | L'instant valide a produit un BusinessYear V1 |

`InvalidInstant` appartient au Value Object d'entrée. L'autorité pure n'a ni policy/configuration externe, ni dépendance infrastructure ; `PolicyUnavailable` et `DependencyUnavailable` seraient donc artificiels.

Les rejets de PropertyTypePolicy restent des résultats/exceptions du Domain appelant.
