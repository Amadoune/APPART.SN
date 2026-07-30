# Historical Redirect PostgreSQL Mapping Matrix

| Colonne | Type | Règle | Modèle applicatif |
|---|---|---|---|
| `decision_id` | `uuid` | identité causale stable | checksum seulement, jamais exposée |
| `historical_canonical` | `text` | URL publique normalisée pour `intact` | `HistoricalCanonical` d'entrée |
| `destination_canonical` | `text NULL` | URL publique normalisée ou absence | `HistoricalRedirectTarget` uniquement si résolu |
| `destination_qualification` | `text NULL` | `current`, `historical`, ou absence atomique | classification Resolved/Chain |
| `revision` | `bigint` | strictement positive pour `intact` | validation d'intégrité |
| `decision_checksum` | `char(64)` | SHA-256 hexadécimal minuscule | validation déterministe |
| `integrity_status` | `text` | `intact` ou `corrupted` | classification Corrupted |

Il n'existe aucune colonne `listing_id` et aucune clé vers un Aggregate métier.
