# Media Item Lifecycle Event Catalog Specification

| Transition certifiée | Type événementiel |
|---|---|
| `Active → Remove → Removed` | `media.item.lifecycle.removed` |
| `Active → Archive → Archived` | `media.item.lifecycle.archived` |

Le mapping est bijectif et fermé. Toute autre transition déclenche un refus explicite du catalogue.
