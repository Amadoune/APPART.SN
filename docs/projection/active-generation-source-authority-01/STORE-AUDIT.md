# Store Audit

Table existante: `public_projection.generations`.

| Colonne | Contrat |
|---|---|
| `generation_id` | UUID, clé primaire |
| `state` | `active`, `candidate` ou `retired` |
| `created_at` | timestamptz(6), valeur durable de création |

L'index partiel unique `public_projection_one_active_generation` impose au plus une Active globale. Les projections référencent la génération avec suppression restrictive. Aucune migration n'est nécessaire.
