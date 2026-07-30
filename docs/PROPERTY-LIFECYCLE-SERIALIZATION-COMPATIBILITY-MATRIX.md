# Property Lifecycle Serialization Compatibility Matrix

| Donnée | Écriture | Lecture | Garantie |
|---|---|---|---|
| `canonicalEvent` | payload JSON opaque | restauration stricte 4.2F | byte-for-byte |
| checksum | SHA-256 du canonique | comparaison obligatoire | identique |
| `eventId` | enveloppe canonique | identité dérivée vérifiée | métier, préservée |
| `message_id` | colonne Outbox | identité Delivery | technique, distincte |
| métadonnées | message et événement | comparaison UTC canonique | exactes |
| type/version | colonnes Delivery | comparaison événement | cohérents |
| propriété/version/ordre | enveloppe Delivery | comparaison payload | cohérents |

Aucune normalisation, enrichissement ou transformation métier n'est autorisé.
