# Listing Publication Outbox Serialization Compatibility Matrix

| Propriété | Écriture | Lecture | Garantie |
|---|---|---|---|
| `message_id` | identité Delivery dérivée | `PublicProjectionDeliveryMessageId` | identité technique exacte |
| `eventId` | dans `canonicalEvent` | payload 4.1EBA | identité métier exacte |
| `eventType` | colonne + événement canonique | enum 4.1EA | égalité vérifiée par le Consumer |
| `payloadVersion` | colonne + événement canonique | V1 | égalité vérifiée |
| `payload` métier | dans `canonicalEvent` | payload 4.1EA | byte-for-byte |
| `occurredAt` / `recordedAt` | colonnes + metadata canonique | instants immuables | même instant et texte canonique conservé |
| checksum | `ListingPublicationDeliveryPayload::checksum` | recalcul du payload restauré | égalité obligatoire |

Les payloads historiques conservent leur sérialisation et leur restauration antérieures.
