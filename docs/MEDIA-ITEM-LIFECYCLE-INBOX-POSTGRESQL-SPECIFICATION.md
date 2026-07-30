# Media Item Lifecycle Inbox PostgreSQL Specification

La table `media.media_item_lifecycle_event_inbox` est créée par la migration additive 033.

| Élément | Garantie |
|---|---|
| clé primaire | `inbox_id`, dérivé par SHA-256 de `messageId` |
| idempotence | unicité stricte de `message_id` |
| événements | types `removed` et `archived` certifiés |
| intégrité | `business_event_id` et `payload_checksum` SHA-256 |
| opacité | `canonical_event` et `transport_envelope` conservés sans transformation |
| reprise | index `(status, message_id)` |
| état initial | `pending`, zéro tentative |

La base ne contient aucune matrice métier. Elle contraint uniquement la forme technique du message certifié.
