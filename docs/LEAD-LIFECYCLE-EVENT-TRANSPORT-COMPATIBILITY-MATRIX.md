# Lead Lifecycle Event Transport Compatibility Matrix

| Élément 4.4E | Représentation transport | Garantie |
|---|---|---|
| enveloppe événementielle complète | `payload.canonicalEvent` | byte-for-byte |
| `eventId` | `metadata.businessEventId` | inchangé |
| type événementiel | `messageType` | égalité stricte |
| octets canoniques | `metadata.payloadChecksum` | SHA-256 |
| identité Delivery | `messageId` | distincte de `eventId` |
| payload V1 | aucune transformation | forme et ordre vérifiés |
| métadonnées métier | incluses dans `canonicalEvent` | aucune régénération |

Les quatre transitions et les trois types certifiés en 4.4E utilisent la même enveloppe V1.
