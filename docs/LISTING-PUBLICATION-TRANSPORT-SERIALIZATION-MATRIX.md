# Listing Publication Transport Serialization Matrix

| Propriété 4.1EA | Position transport | Restauration | Vérification |
|---|---|---|---|
| `eventId` | `canonicalEvent.eventId` | `ListingPublicationEventId` dérivé | égalité exacte |
| `eventType` | `canonicalEvent.eventType` | enum fermé | valeur reconnue |
| `payloadVersion` | `canonicalEvent.payloadVersion` | enum fermé | V1 reconnu |
| `payload` | `canonicalEvent.payload` | payload immuable 4.1EA | clés, types et ordre exacts |
| `metadata` | `canonicalEvent.metadata` | métadonnées 4.1EA | UTC canonique et ordre causal |
| enveloppe complète | `canonicalEvent` | événement complet | resérialisation byte-for-byte |
| intégrité transport | `checksum` | SHA-256 | recalcul exact |

La matrice ne définit aucune colonne PostgreSQL et ne modifie aucun mapper Outbox au Sprint 4.1EBA.
