# Reservation Lifecycle Inbox PostgreSQL Specification

## Propriété

La table `reservation_lifecycle.reservation_lifecycle_event_inbox` appartient exclusivement à Reservation Lifecycle. Elle est append-only dans l'adaptateur : aucun `UPDATE` ni `DELETE` n'est exécuté en production.

| Colonne | Contrainte |
|---|---|
| `inbox_id` | clé primaire `rlei:<sha256(messageId)>` |
| `message_id` | identité transport V1, `NOT NULL UNIQUE` |
| `message_type` | type exact de l'enveloppe |
| `transport_version` | valeur exacte `1` |
| `canonical_event` | octets métier 4.3E inchangés |
| `source` | valeur exacte `ReservationLifecycle` |
| `business_event_id` | identité métier 4.3E inchangée |
| `payload_checksum` | SHA-256 de `canonical_event` |
| `transport_envelope` | sérialisation canonique complète 4.3F |

L'index `reservation_lifecycle_event_inbox_restore_lookup` porte sur `(message_type, transport_version, message_id)`. Il fournit un ordre exact pour la recherche et la restauration par type/version.

La table n'embarque aucune transition, matrice, règle métier, horloge ou trigger.
