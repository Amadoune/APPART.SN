# Place Lifecycle — Matrice bijective transitions ↔ événements

| État précédent | Action | État résultant | Fait V1 unique | Extension minimale |
|---|---|---|---|---|
| `Disabled` | `Enable` | `Enabled` | `place.lifecycle.enabled` | aucune |
| `Enabled` | `Disable` | `Disabled` | `place.lifecycle.disabled` | aucune |
| `Enabled` | `Merge` | `Merged` | `place.lifecycle.merged` | `targetPlaceId` |
| `Disabled` | `Merge` | `Merged` | `place.lifecycle.merged` | `targetPlaceId` |

Toutes les autres combinaisons sont non certifiées et refusées. La combinaison
du type, des états et de l'action rend chaque fait bijectif avec sa transition,
y compris les deux origines autorisées de `Merged`.
