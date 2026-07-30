# Reservation Lifecycle Routing Contract Compatibility Matrix

| Élément | 4.3F | 4.3F-R1 | Compatibilité |
|---|---|---|---|
| Paramètre `route` | `ReservationLifecycleTransportEnvelope` | identique | totale |
| Retour `route` | `void` | `ReservationLifecycleRoutingResult` | amendé intentionnellement |
| `canonicalEvent` | chaîne canonique 4.3E | identique | byte-for-byte |
| `messageId` | SHA-256 technique | identique | totale |
| `messageType` | type 4.3E | identique | totale |
| `transportVersion` | `1` | identique | totale |
| `businessEventId` | `eventId` inchangé | identique | totale |
| `payloadChecksum` | SHA-256 de `canonicalEvent` | identique | totale |
| Serializer | ordre canonique V1 | identique | byte-for-byte |
| Implémentation de port | aucune | aucune | totale |
| PostgreSQL / Inbox / Outbox | absent | absent | totale |

L'amendement ne touche aucun octet transporté. Il complète uniquement l'observabilité contractuelle du futur appel de routage.
