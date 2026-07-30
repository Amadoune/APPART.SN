# Place Lifecycle Event Transport — Spécification

## Payload Delivery opaque

Le payload de livraison expose une seule propriété :

```text
canonicalEvent: string
```

Cette chaîne contient exactement la représentation canonique du contrat
événementiel 4.8F. Le transport n'interprète aucune règle métier et ne modifie
aucun champ.

## Identités séparées

```text
eventId
→ identité métier certifiée par 4.8F

messageId
→ identité de transport
→ place-lifecycle-delivery-{SHA-256(transportVersion + payloadChecksum)}
```

Les deux identités ont des types, des formats et des responsabilités distincts.

## Checksum

```text
payloadChecksum = SHA-256(canonicalEvent)
```

Il couvre exactement les octets du fait opaque.

## Enveloppe V1

Ordre canonique :

```text
messageId
messageType
transportVersion
payload
metadata
```

Les métadonnées contiennent exclusivement :

```text
source
eventId
payloadChecksum
```

## Restauration exacte

La restauration :

1. valide la forme fermée de l'enveloppe;
2. restaure le fait V1 et recalcule son identité;
3. recalcule checksum et `messageId`;
4. reconstruit l'enveloppe;
5. exige que sa sérialisation soit strictement identique aux octets reçus.

Toute altération ou représentation JSON non canonique est refusée.

## Frontière

Le sprint fournit uniquement le port `PlaceLifecycleEventRouter`. Aucun routeur
concret, publication, Inbox, Outbox, Consumer, Worker, Runtime ou HTTP n'est
créé.
