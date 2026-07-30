# Reservation Lifecycle Atomic Event Integration Analysis

## Décision

`ReservationLifecycleAtomicEventOrchestrator` enveloppe l'orchestrateur 4.3D dans la transaction Aggregate + Outbox PostgreSQL existante. Il ne décide aucune transition : il transforme uniquement un résultat persisté `Applied` ou `AlreadyApplied` en l'unique événement défini par le catalogue 4.3E, puis en message Delivery compatible 4.3H.

Les instants `occurredAt` et `recordedAt` sont fournis explicitement par la requête d'intégration. Ils servent uniquement au message Delivery, car l'événement canonique Reservation 4.3E ne contient volontairement aucun timestamp. Aucune horloge système n'est consultée.

## Frontières

- workflow, store, événement, transport, catalogue Delivery et Consumer inchangés ;
- aucune publication directe, HTTP, Projection ou Worker spécifique ;
- une seule transaction et une seule Outbox ;
- toute corruption de persistance ou tout refus Outbox force le rollback global.
