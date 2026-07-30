# Lead Lifecycle Transition Matrix

| État source | Action | État cible |
|---|---|---|
| `Created` | `Deliver` | `Delivered` |
| `Created` | `Reject` | `Rejected` |
| `Delivered` | `Close` | `Closed` |
| `Rejected` | `Close` | `Closed` |

Cette matrice est exhaustive. Toute combinaison absente est refusée et ne construit aucune transition.
