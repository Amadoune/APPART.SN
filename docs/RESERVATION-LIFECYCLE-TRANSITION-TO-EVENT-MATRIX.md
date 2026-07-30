# Reservation Lifecycle Transition to Event Matrix

| État précédent | Action | État courant | Événement unique |
|---|---|---|---|
| `draft` | `submit` | `requested` | `ReservationSubmitted` |
| `draft` | `cancel` | `cancelled` | `ReservationCancelledFromDraft` |
| `requested` | `confirm` | `confirmed` | `ReservationConfirmed` |
| `requested` | `reject` | `rejected` | `ReservationRejected` |
| `requested` | `cancel` | `cancelled` | `ReservationCancelledFromRequested` |
| `requested` | `expire` | `expired` | `ReservationExpiredFromRequested` |
| `confirmed` | `start` | `in_progress` | `ReservationStarted` |
| `confirmed` | `cancel` | `cancelled` | `ReservationCancelledFromConfirmed` |
| `confirmed` | `expire` | `expired` | `ReservationExpiredFromConfirmed` |
| `in_progress` | `complete` | `completed` | `ReservationCompleted` |
| `in_progress` | `cancel` | `cancelled` | `ReservationCancelledInProgress` |

La matrice est totale sur les onze transitions autorisées de 4.3A et bijective : chaque ligne produit un seul type, et chaque type appartient à une seule ligne. Les cinquante-trois couples refusés par le workflow et toute transition construite hors matrice ne produisent aucun événement.
