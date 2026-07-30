# Historical Canonical Qualification PostgreSQL Mapping Matrix

| Colonne | Type | Règle | Usage |
|---|---|---|---|
| `decision_id` | `uuid` | identité stable | ordre et checksum |
| `canonical` | `text` | URL APPART.SN normalisée si intacte | identité qualifiée |
| `qualification` | `text` | `current` ou `historical` si intacte | statut applicatif |
| `revision` | `bigint` | strictement positive si intacte | intégrité |
| `decision_checksum` | `char(64)` | SHA-256 hexadécimal | déterminisme |
| `integrity_status` | `text` | `intact` ou `corrupted` | classification Corrupted |

Aucune destination, aucun `ListingId` et aucune référence vers un Aggregate ne sont stockés.
