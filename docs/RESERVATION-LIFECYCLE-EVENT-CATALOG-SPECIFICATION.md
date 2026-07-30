# Reservation Lifecycle Event Catalog Specification

## Catalogue fermé V1

| Type PHP | Type canonique |
|---|---|
| `ReservationSubmitted` | `reservation.lifecycle.submitted` |
| `ReservationCancelledFromDraft` | `reservation.lifecycle.cancelled_from_draft` |
| `ReservationConfirmed` | `reservation.lifecycle.confirmed` |
| `ReservationRejected` | `reservation.lifecycle.rejected` |
| `ReservationCancelledFromRequested` | `reservation.lifecycle.cancelled_from_requested` |
| `ReservationExpiredFromRequested` | `reservation.lifecycle.expired_from_requested` |
| `ReservationStarted` | `reservation.lifecycle.started` |
| `ReservationCancelledFromConfirmed` | `reservation.lifecycle.cancelled_from_confirmed` |
| `ReservationExpiredFromConfirmed` | `reservation.lifecycle.expired_from_confirmed` |
| `ReservationCompleted` | `reservation.lifecycle.completed` |
| `ReservationCancelledInProgress` | `reservation.lifecycle.cancelled_in_progress` |

`ReservationLifecycleEventType` est un enum fermé. L'ajout d'un type ou d'une transition exige une évolution contractuelle ultérieure. `ReservationLifecycleEventPayloadVersion::V1` est l'unique version admise et `ReservationLifecycleEventAggregateType::ReservationLifecycle` l'unique aggregate type.

`ReservationLifecycleEventCatalog::eventFor()` retourne exactement un événement pour une transition certifiée et lève une exception contractuelle pour toute autre transition. Il ne consulte jamais le workflow.
