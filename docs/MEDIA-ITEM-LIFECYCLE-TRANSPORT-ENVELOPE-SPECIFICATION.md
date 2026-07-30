# Media Item Lifecycle Transport Envelope V1 Specification

Ordre canonique :

1. `messageId` ;
2. `messageType` ;
3. `transportVersion` ;
4. `payload` ;
5. `metadata`.

Métadonnées :

- `source = MediaItemLifecycle` ;
- `businessEventId = eventId` ;
- `payloadChecksum = SHA-256(canonicalEvent)`.

`messageId` et `businessEventId` ne peuvent jamais être égaux. La version de transport est exactement 1.
