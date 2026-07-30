# Reservation Lifecycle Transition Matrix

| État source | Action | État cible |
|---|---|---|
| `Draft` | `Submit` | `Requested` |
| `Draft` | `Cancel` | `Cancelled` |
| `Requested` | `Confirm` | `Confirmed` |
| `Requested` | `Reject` | `Rejected` |
| `Requested` | `Cancel` | `Cancelled` |
| `Requested` | `Expire` | `Expired` |
| `Confirmed` | `Start` | `InProgress` |
| `Confirmed` | `Cancel` | `Cancelled` |
| `Confirmed` | `Expire` | `Expired` |
| `InProgress` | `Complete` | `Completed` |
| `InProgress` | `Cancel` | `Cancelled` |

Ces onze transitions forment la matrice normative exhaustive. Toute combinaison absente est refusée ; aucune transition de sortie n'existe depuis un état terminal.
