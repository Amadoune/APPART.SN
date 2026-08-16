# Store audit

Store existant : `public_media.decisions` (migration `011_public_media_decisions.sql`).

| Élément | Définition |
|---|---|
| PK/lookup | `media_collection_id text` |
| version | entier strictement positif |
| identité causale | `causation_key` non vide |
| intégrité | `revision_checksum` et `payload_checksum`, SHA-256, égaux |
| payload | JSONB |
| concurrence | advisory lock + `FOR UPDATE` |

Structure présente; aucune ligne pour la collection RC2. Aucune migration nécessaire pour le store existant.
