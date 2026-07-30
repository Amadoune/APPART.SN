# Reservation Lifecycle Transport Restoration Matrix

| Élément | Source persistée | Restauration | Garantie |
|---|---|---|---|
| `canonicalEvent` | payload JSONB | `ReservationLifecycleDeliveryPayload::restore` | byte-for-byte |
| `payloadChecksum` | colonne Outbox | checksum SHA-256 du payload restauré | égalité obligatoire |
| `businessEventId` | événement canonique | métadonnée dérivée par l'enveloppe certifiée | identité inchangée |
| `messageId` Delivery | colonne Outbox | mapper générique | identité technique inchangée |
| enveloppe transport | payload restauré | `ReservationLifecycleTransportEnvelope::wrap` | type, version et métadonnées déterministes |

La matrice s'applique identiquement aux onze types. Aucune transition n'est reconstruite par le mapper ou le Consumer ; la restauration est déléguée au contrat de transport 4.3F.
