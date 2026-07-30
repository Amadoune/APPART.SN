# Listing Publication Identity Separation Specification

| Identité | Propriétaire | Source | Responsabilité |
|---|---|---|---|
| `eventId` | Listing Publication 4.1EA | dérivation métier déterministe | identifier le fait métier |
| `message_id` | Public Projection Delivery | clé d'idempotence technique | identifier la livraison Outbox |
| `idempotency_key` | Public Projection Delivery | module, aggregate, version, index, type, version payload | dédupliquer le transport |

`eventId` reste à l'intérieur de l'événement canonique transporté. Il n'est jamais converti en `message_id`. L'identité technique ne remplace jamais l'identité métier.
