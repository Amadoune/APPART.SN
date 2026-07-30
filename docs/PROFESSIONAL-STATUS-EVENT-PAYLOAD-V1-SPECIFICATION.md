# Professional Status Event Payload V1 Specification

Le payload contient, dans un ordre fixe : `eventId`, `aggregateType`, `professionalId`, `transition`, `previousState`, `currentState`, `action`, `version`, `occurredVersion`.

`aggregateType` vaut `ProfessionalStatus` et `version` vaut 1. `occurredVersion` correspond à la version persistée de la transition et doit être au moins 2.

Les métadonnées contiennent `eventType`, `payloadVersion`, `actorId`, `occurredAt` et `recordedAt`. Les deux instants sont UTC explicites et `recordedAt` ne peut précéder `occurredAt`.
