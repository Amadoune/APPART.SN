# Reservation Lifecycle Transport Envelope Specification

## Enveloppe V1

| Ordre | Champ | Règle |
|---:|---|---|
| 1 | `messageId` | identité technique `reservation-lifecycle-delivery-<sha256(canonicalEvent)>` |
| 2 | `messageType` | type canonique de l'événement 4.3E, conservé sans transformation |
| 3 | `transportVersion` | entier fixe `1` |
| 4 | `payload` | `ReservationLifecycleDeliveryPayload` |
| 5 | `metadata` | `ReservationLifecycleDeliveryMetadata` |

## Métadonnées techniques

| Champ | Valeur |
|---|---|
| `source` | valeur fixe `ReservationLifecycle` |
| `businessEventId` | `eventId` certifié, inchangé |
| `payloadChecksum` | SHA-256 des octets de `canonicalEvent` |

Le constructeur protège la concordance du type, de la version, de l'identité métier et du checksum. Aucun instant technique n'est ajouté. `messageId` et `eventId` sont explicitement distincts et ne sont jamais interchangeables.
