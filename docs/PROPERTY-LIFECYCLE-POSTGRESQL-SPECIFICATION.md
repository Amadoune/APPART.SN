# Property Lifecycle PostgreSQL Specification

## Table

`real_estate_catalog.property_lifecycle_transitions`

| Colonne | Type | Règle |
|---|---|---|
| `property_id` | UUID | identité propriétaire du journal |
| `version` | bigint | strictement positive, clé primaire avec l'identité |
| `previous_state` | text nullable | absent uniquement à l'initialisation |
| `current_state` | text | l'un des six états certifiés |
| `action` | text nullable | absente uniquement à l'initialisation |
| `transition_checksum` | char(64) | SHA-256 hexadécimal déterministe |

L'index `(property_id, version DESC) INCLUDE (current_state, transition_checksum)` garantit une lecture courante exacte, ordonnée et bornée.

La contrainte `property_lifecycle_allowed_transition` contient exactement les douze triplets 4.2A. Aucune horloge ni identité technique additionnelle n'est stockée.
