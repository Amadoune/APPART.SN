# Lead Lifecycle Event Catalog Specification

Le catalogue V1 est fermé à trois faits : `lead.lifecycle.delivered`, `lead.lifecycle.rejected` et `lead.lifecycle.closed`.

| Transition certifiée | Événement |
|---|---|
| `Created → Deliver → Delivered` | `lead.lifecycle.delivered` |
| `Created → Reject → Rejected` | `lead.lifecycle.rejected` |
| `Delivered → Close → Closed` | `lead.lifecycle.closed` |
| `Rejected → Close → Closed` | `lead.lifecycle.closed` |

Les deux chemins vers `Closed` représentent le même fait métier. Leur origine exacte reste observable dans `previousState` et `transition`. Toute transition absente de cette matrice est refusée explicitement.
