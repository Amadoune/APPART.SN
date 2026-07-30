# Media Item Lifecycle Contextual PostgreSQL Specification

Table additive : `media.media_item_lifecycle_transition_contexts`.

| Colonne | Garantie |
|---|---|
| `media_id`, `version` | clé primaire et corrélation exacte avec l'append |
| `contract_version` | exclusivement V1 |
| `collection_id`, `collection_version` | provenance logique explicite de la décision |
| `actor_id`, `occurred_at` | contexte explicite |
| `primary_disposition` | `not_primary` ou `replacement_selected` |
| `replacement_media_id` | nul uniquement pour `not_primary`, obligatoire et distinct sinon |
| `context_checksum` | SHA-256 canonique certifié en 4.6C-R1 |

L'absence volontaire de clé étrangère préserve le rollback autonome du journal historique 031. L'atomicité est garantie par le repository transactionnel, et l'intégrité de rejeu par les deux checksums.
