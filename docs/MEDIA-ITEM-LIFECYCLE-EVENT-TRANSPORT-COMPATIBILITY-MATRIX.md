# Media Item Lifecycle Event Transport Compatibility Matrix

| Événement 4.6E | Payload Delivery | Transport |
|---|---|---|
| `media.item.lifecycle.removed` V1 | `canonicalEvent` opaque | enveloppe V1 |
| `media.item.lifecycle.archived` V1 | `canonicalEvent` opaque | enveloppe V1 |

Les deux événements partagent exactement les mêmes règles de checksum, restauration, métadonnées et routage. Aucun mapping métier parallèle n'existe dans le transport.
