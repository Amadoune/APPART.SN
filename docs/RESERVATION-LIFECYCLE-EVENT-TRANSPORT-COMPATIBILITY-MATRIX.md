# Reservation Lifecycle Event Transport Compatibility Matrix

| Événement 4.3E | `messageType` V1 | Payload exact | Compatible |
|---|---|---|---|
| `ReservationSubmitted` | `reservation.lifecycle.submitted` | `canonicalEvent` | oui |
| `ReservationCancelledFromDraft` | `reservation.lifecycle.cancelled_from_draft` | `canonicalEvent` | oui |
| `ReservationConfirmed` | `reservation.lifecycle.confirmed` | `canonicalEvent` | oui |
| `ReservationRejected` | `reservation.lifecycle.rejected` | `canonicalEvent` | oui |
| `ReservationCancelledFromRequested` | `reservation.lifecycle.cancelled_from_requested` | `canonicalEvent` | oui |
| `ReservationExpiredFromRequested` | `reservation.lifecycle.expired_from_requested` | `canonicalEvent` | oui |
| `ReservationStarted` | `reservation.lifecycle.started` | `canonicalEvent` | oui |
| `ReservationCancelledFromConfirmed` | `reservation.lifecycle.cancelled_from_confirmed` | `canonicalEvent` | oui |
| `ReservationExpiredFromConfirmed` | `reservation.lifecycle.expired_from_confirmed` | `canonicalEvent` | oui |
| `ReservationCompleted` | `reservation.lifecycle.completed` | `canonicalEvent` | oui |
| `ReservationCancelledInProgress` | `reservation.lifecycle.cancelled_in_progress` | `canonicalEvent` | oui |

Les onze types empruntent le même format de transport V1. Le type reste distinct afin que le futur routage puisse être explicite. Aucun événement générique, inconnu ou non certifié n'est accepté par la restauration du contrat métier.

Le port `ReservationLifecycleEventRouterPort` reçoit `ReservationLifecycleTransportEnvelope`. Son retour initialement `void` en 4.3F est remplacé par le résultat fermé `ReservationLifecycleRoutingResult` par l'amendement 4.3F-R1. Aucune instance, aucun binding et aucun appel n'existent encore.
