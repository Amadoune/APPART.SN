# Lead Lifecycle Transport Envelope V1 Specification

L'enveloppe V1 contient, dans cet ordre :

1. `messageId` ;
2. `messageType` ;
3. `transportVersion` ;
4. `payload` ;
5. `metadata`.

`messageId` suit `lead-lifecycle-delivery-{sha256}` et dérive des octets canoniques. `messageType` reprend le type événementiel certifié. `transportVersion` vaut `1`.

Les métadonnées contiennent `source = LeadLifecycle`, `businessEventId = eventId` et `payloadChecksum`. Aucun instant, acteur ou identifiant n'est généré par le transport.
