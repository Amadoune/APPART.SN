# Visibility Interaction

| Visibilité | Rank/facets calculés | Effet public |
|---|---|---|
| Visible | `0`, `[]` | applicables à la projection |
| Hidden | `0`, `[]` | sans effet de classement public |
| Removed | `0`, `[]` | sans effet de classement public |

La policy de ranking ne remplace jamais `SearchVisibilityPolicy`. Conserver une sortie déterministe pour Hidden/Removed respecte la structure actuelle de `SearchProjection` et facilite le replay.
