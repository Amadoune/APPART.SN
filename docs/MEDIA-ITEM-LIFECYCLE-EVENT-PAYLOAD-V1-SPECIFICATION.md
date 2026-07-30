# Media Item Lifecycle Event Payload V1 Specification

Ordre canonique du payload :

1. `eventId` ;
2. `aggregateType = MediaItemLifecycle` ;
3. `mediaId` ;
4. `transition` ;
5. `previousState` ;
6. `currentState` ;
7. `action` ;
8. `version = 1` ;
9. `occurredVersion`.

Métadonnées canoniques :

1. `eventType` ;
2. `payloadVersion` ;
3. `actorId` ;
4. `occurredAt` UTC ;
5. `recordedAt` UTC.

`recordedAt` ne peut pas précéder `occurredAt`. Aucun instant ou acteur n'est généré implicitement.
