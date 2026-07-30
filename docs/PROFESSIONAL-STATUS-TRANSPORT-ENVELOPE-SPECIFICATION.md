# Professional Status Transport Envelope Specification

L'enveloppe V1 ordonne strictement :

```text
messageId → messageType → transportVersion → payload → metadata
```

`messageId` est une identité technique déterministe préfixée `professional-status-delivery-`. Elle demeure distincte de l'`eventId` métier conservé comme `businessEventId`. Les métadonnées contiennent exclusivement `source`, `businessEventId` et `payloadChecksum`.
