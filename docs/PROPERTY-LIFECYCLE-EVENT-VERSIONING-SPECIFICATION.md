# Property Lifecycle Event Versioning Specification

## Version initiale

`PropertyLifecycleEventPayloadVersion::V1` vaut 1 et fige le nom, le type, la présence, l'ordre et la sémantique des cinq champs du payload.

## Identité

`eventId` est `property-lifecycle-` suivi du SHA-256 de :

`propertyId|lifecycleVersion|eventType|payloadVersion`

Il est stable au rejeu et varie avec chaque composante identitaire. Aucun UUID, compteur ou aléatoire n'est utilisé.

## Enveloppe canonique

L'ordre JSON est `eventId`, `eventType`, `payloadVersion`, `payload`, `metadata`. UTF-8, les slashs et Unicode sont préservés sans échappement superflu. Toute évolution incompatible impose V2 et ne peut réinterpréter V1.
