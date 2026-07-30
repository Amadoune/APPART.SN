# Account Status — Event Transport V1

`AccountStatusEventV1` est encapsulé comme événement JSON canonique opaque dans
`AccountStatusDeliveryPayload`, puis dans `AccountStatusDeliveryMessage`.

```text
messageId
messageType
transportVersion = 1
payload.canonicalEvent
metadata.source = AccountStatus
metadata.eventId
metadata.payloadChecksum
```

`eventId` demeure l'identité métier. `messageId` est une identité technique
distincte, dérivée de la version de transport et du checksum SHA-256 du payload
canonique. Le serializer impose un round-trip byte-for-byte et rejette toute
forme, identité, version, checksum ou encodage divergent.

Le payload implémente directement le port générique
`PublicProjectionDeliveryPayload`; aucun adaptateur Account Status n'est créé.

Le transport n'introduit aucun Routing, Inbox, Outbox, Consumer, Worker,
publication ou HTTP et ne contient aucune donnée privée supplémentaire.
