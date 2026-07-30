# Lead Lifecycle PostgreSQL Specification

## Journal propriétaire

`contacts_leads.lead_lifecycle_transitions` contient exactement :

| Colonne | Type | Invariant |
|---|---|---|
| `lead_id` | `uuid` | identité du flux |
| `version` | `bigint` | strictement positive, clé primaire composite |
| `previous_state` | `text nullable` | nul uniquement à l'initialisation |
| `current_state` | `text` | état fermé 4.4A |
| `action` | `text nullable` | nulle uniquement à l'initialisation |
| `transition_checksum` | `char(64)` | SHA-256 hexadécimal |

La lecture courante est `ORDER BY version DESC LIMIT 1` et dispose de l'index `lead_lifecycle_current_state_lookup`.

Les écritures utilisent `pg_advisory_xact_lock(hashtextextended(lead_id, 0))`. Le repository rejoint une transaction existante ou ouvre sa propre transaction locale.
