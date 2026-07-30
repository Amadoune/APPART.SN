# Reservation Lifecycle Transport Serialization Specification

La sérialisation canonique utilise l'ordre racine suivant :

```text
messageId
messageType
transportVersion
payload
metadata
```

`payload` contient uniquement `canonicalEvent`. `metadata` contient, dans cet ordre, `source`, `businessEventId`, `payloadChecksum`.

L'encodage utilise JSON UTF-8 avec `JSON_THROW_ON_ERROR`, `JSON_UNESCAPED_SLASHES` et `JSON_UNESCAPED_UNICODE`. Aucun tri dynamique, champ optionnel, timestamp implicite ou formatage décoratif n'est autorisé.

La chaîne métier 4.3E est encodée comme une valeur JSON du transport : une fois décodée, elle est strictement égale aux octets d'origine. Le serializer de transport lit les identités certifiées; il ne dérive jamais l'`eventId` métier.

Cette spécification ne définit ni persistance, ni protocole réseau, ni acquittement, ni résultat de routage.
