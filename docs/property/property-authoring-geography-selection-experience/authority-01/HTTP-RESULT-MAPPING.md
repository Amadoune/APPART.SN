# HTTP Result Mapping

| Entrée / statut F1 | HTTP | Corps |
|---|---:|---|
| query invalide | 422 | problème fermé, aucune donnée Geography |
| `Available` | 200 | `items` et `nextCursor` |
| `Empty` | 200 | `items: []`, `nextCursor: null` |
| `Missing` | 404 | problème parent absent |
| `Corrupted` | 500 | problème corruption, aucun fallback |
| `DependencyUnavailable` | 503 | problème indisponibilité, retry autorisé |

La distinction Corrupted/DependencyUnavailable repose sur les codes 500/503 ; aucun statut propriétaire supplémentaire n’est inventé.

## DTO de succès

Chaque item contient exactement :

- `placeId` ;
- `label` ;
- `type` ;
- `parentPlaceId`.

L’enveloppe contient seulement `items` et `nextCursor`. Aucun Aggregate, alias, breadcrumb, coordonnées, état interne ou donnée Projection.

Les erreurs utilisent une réponse problème minimale sans reproduire la query ou révéler les dépendances internes.
