# Media Item Lifecycle Transition to Event Matrix

| État précédent | Action | État courant | Événement | Cardinalité |
|---|---|---|---|---|
| `Active` | `Remove` | `Removed` | `media.item.lifecycle.removed` | 1 ↔ 1 |
| `Active` | `Archive` | `Archived` | `media.item.lifecycle.archived` | 1 ↔ 1 |

Les états terminaux ne produisent aucune transition supplémentaire. Aucune information de collection n'intervient dans le mapping.
