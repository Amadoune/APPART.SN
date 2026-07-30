# Media Item Lifecycle Outbox Mapper Compatibility

Le Writer générique persiste les champs opaques de `MediaItemLifecycleDeliveryPayload`. Le Reader sélectionne le restaurateur depuis le type événementiel fermé puis appelle :

```text
MediaItemLifecycleDeliveryPayload::restore(data)
```

Le round-trip conserve :

- `canonicalEvent` byte-for-byte ;
- le checksum du payload ;
- `eventId` métier ;
- `messageId` technique ;
- l'owner `Media` ;
- l'ordre causal et les instants explicites.

Aucune transition ou donnée `MediaCollection` n'est reconstruite.
