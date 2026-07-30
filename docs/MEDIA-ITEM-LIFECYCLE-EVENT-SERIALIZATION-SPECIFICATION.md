# Media Item Lifecycle Event Serialization Specification

L'enveloppe canonique ordonne :

1. `eventId` ;
2. `eventType` ;
3. `payloadVersion` ;
4. `payload` ;
5. `metadata`.

La sérialisation utilise JSON sans échappement des slashs ni des caractères Unicode. Le même objet produit les mêmes octets.

`eventId` est le SHA-256 de :

```text
eventType
payloadVersion
mediaId
previousState
action
currentState
occurredVersion
```

Les champs sont séparés par LF. L'identité est indépendante de tout futur transport.
