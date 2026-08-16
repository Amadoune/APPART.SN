# Store Audit

Store : `content_seo.public_source_snapshots`.

| Colonne | Sémantique |
|---|---|
| `listing_id` | clé primaire et lookup owner |
| `snapshot_id` | UUID unique |
| `version` | entier strictement positif |
| `payload` | JSONB canonique |
| `payload_checksum` | SHA-256 hexadécimal |
| `updated_at` | métadonnée technique |

La migration 009 est déployée. Aucun état lifecycle séparé n’est stocké : les états owner figurent dans le payload.
