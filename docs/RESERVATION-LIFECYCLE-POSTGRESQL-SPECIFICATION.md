# Reservation Lifecycle PostgreSQL Specification

La table propriétaire est `reservation_lifecycle.reservation_lifecycle_transitions`.

| Colonne | Type | Invariant |
|---|---|---|
| `reservation_id` | UUID | non nul, composante de clé primaire |
| `version` | bigint | strictement positive, composante de clé primaire |
| `previous_state` | text nullable | absent uniquement à la version initiale |
| `current_state` | text | l'un des huit états fermés |
| `action` | text nullable | absente uniquement à la version initiale |
| `transition_checksum` | char(64) | SHA-256 hexadécimal |

La contrainte `reservation_lifecycle_allowed_transition` énumère exactement les onze transitions 4.3A. L'index `(reservation_id, version DESC)` avec inclusion de l'état et du checksum sert la lecture exacte et bornée.

Aucun timestamp n'ordonne le métier et aucune mise à jour ou suppression n'appartient au repository.
