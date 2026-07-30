# Place Lifecycle Event Transport — Matrice de compatibilité

| Contrat | Valeur V1 | Règle |
|---|---|---|
| payload Delivery | `canonicalEvent` | opaque, propriété unique |
| transportVersion | `1` | version fermée |
| messageType | type événement 4.8F | égalité exacte |
| eventId | SHA-256 métier 4.8F | jamais recalculé comme `messageId` |
| payloadChecksum | SHA-256 des octets du fait | déterministe |
| messageId | préfixe + SHA-256 version/checksum | identité transport distincte |
| source | `PlaceLifecycle` | constante |
| restauration | octets identiques | aucune normalisation tolérée |

Les trois types événementiels V1 et les quatre transitions certifiées sont
restaurables sans perte.
