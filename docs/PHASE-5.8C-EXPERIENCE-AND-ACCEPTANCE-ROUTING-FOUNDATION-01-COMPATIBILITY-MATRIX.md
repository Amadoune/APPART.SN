# Compatibility Matrix

La dépendance autorisée unique est l'enveloppe Transport V1 certifiée. Outbox, Delivery, Event, Reader, OwnerSource, Runtime, HTTP, Infrastructure et Consumer sont interdits au Routing.

Le résultat conserve par identité l'enveloppe et donc messageId, eventId, EventType, status, observedAt et checksum.

