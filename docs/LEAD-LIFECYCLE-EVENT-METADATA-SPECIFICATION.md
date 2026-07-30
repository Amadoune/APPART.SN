# Lead Lifecycle Event Metadata Specification

Les métadonnées obligatoires sont `eventType`, `payloadVersion`, `actorId`, `occurredAt` et `recordedAt`, dans cet ordre.

`actorId`, `occurredAt` et `recordedAt` sont fournis explicitement. Aucun `now()`, acteur implicite ou identifiant aléatoire n'est autorisé. Les instants sont UTC et `recordedAt` ne peut précéder `occurredAt`.

Ces métadonnées décrivent le fait et son enregistrement contractuel ; elles n'autorisent encore aucun transport ni aucune publication.
