# Professional Status Outbox Mapping Specification

Les deux événements `professional.status.suspended` et `professional.status.reactivated` sont associés à `Professionals / ProfessionalStatus / V1 / ProfessionalStatusDeliveryPayload`.

Le mapper conserve et restaure `canonicalEvent` byte-for-byte, vérifie le checksum et maintient la séparation entre `eventId` métier et `messageId` technique.
