# Listing Publication Delivery Payload Specification

## Contrat

`ListingPublicationDeliveryPayload` est `final readonly` et implémente le contrat de transport existant `PublicProjectionDeliveryPayload`.

Sa représentation technique est strictement :

```json
{"canonicalEvent":"<JSON canonique 4.1EA>"}
```

Le contenu de `canonicalEvent` conserve dans leur ordre normatif `eventId`, `eventType`, `payloadVersion`, `payload` et `metadata`. Aucun champ métier n'est ajouté, supprimé, renommé ou normalisé.

## Intégrité

Le checksum est `SHA-256(canonicalEvent)`. À la restauration, l'adaptateur exige la forme exacte, reconstruit les Value Objects certifiés, recalcule l'identité métier et exige que la resérialisation soit strictement égale à l'entrée.
