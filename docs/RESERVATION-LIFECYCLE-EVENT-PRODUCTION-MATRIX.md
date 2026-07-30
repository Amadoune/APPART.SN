# Reservation Lifecycle Event Production Matrix

| Transition | Événement |
|---|---|
| Draft → Requested (`Submit`) | `reservation.lifecycle.submitted` |
| Draft → Cancelled (`Cancel`) | `reservation.lifecycle.cancelled_from_draft` |
| Requested → Confirmed (`Confirm`) | `reservation.lifecycle.confirmed` |
| Requested → Rejected (`Reject`) | `reservation.lifecycle.rejected` |
| Requested → Cancelled (`Cancel`) | `reservation.lifecycle.cancelled_from_requested` |
| Requested → Expired (`Expire`) | `reservation.lifecycle.expired_from_requested` |
| Confirmed → InProgress (`Start`) | `reservation.lifecycle.started` |
| Confirmed → Cancelled (`Cancel`) | `reservation.lifecycle.cancelled_from_confirmed` |
| Confirmed → Expired (`Expire`) | `reservation.lifecycle.expired_from_confirmed` |
| InProgress → Completed (`Complete`) | `reservation.lifecycle.completed` |
| InProgress → Cancelled (`Cancel`) | `reservation.lifecycle.cancelled_in_progress` |

Cette matrice est consommée exclusivement via `ReservationLifecycleEventCatalog`; elle n'est pas recodée dans l'intégrateur.
