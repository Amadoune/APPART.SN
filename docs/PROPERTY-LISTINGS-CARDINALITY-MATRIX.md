# Matrice de cardinalité et de parcours

| Listings liés | Limite | Première lecture | Suite |
|---:|---:|---|---|
| 0 | toute limite positive | `Empty` | aucune |
| 1 | >= 1 | `Completed` avec 1 identité | aucune |
| N <= limite | limite | `Completed` avec N identités | aucune |
| N > limite | limite | `Found` avec limite identités | checkpoint jusqu'à `Completed` |

Les Listings d'une autre Property ne sont jamais retournés. Aucun statut fonctionnel du Listing ne
réduit la cardinalité persistée.
