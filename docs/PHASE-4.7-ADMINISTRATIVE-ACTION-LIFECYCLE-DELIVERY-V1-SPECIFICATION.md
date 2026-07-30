# Phase 4.7F — Administrative Action Lifecycle Delivery V1

## Payload opaque

```text
canonicalEvent: string
```

`canonicalEvent` correspond exactement aux octets JSON canoniques produits par 4.7E.

## Enveloppe

Ordre canonique :

1. `messageId` ;
2. `messageType` ;
3. `transportVersion` ;
4. `payload` ;
5. `metadata`.

Métadonnées :

1. `source = AdministrationAudit` ;
2. `businessEventId` ;
3. `payloadChecksum`.

## Intégrité

* `transportVersion = 1` ;
* `payloadChecksum = SHA-256(canonicalEvent)` ;
* `messageId = administrative-action-lifecycle-delivery-` suivi du même SHA-256 ;
* la restauration valide la forme, l'identité 4.7E, le checksum événementiel et l'égalité byte-for-byte.
