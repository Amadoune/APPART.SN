# Compatibility Matrix — Legacy Migration Persistence

| Surface | Dépendance autorisée | Dépendance interdite | Statut |
|---|---|---|---|
| Application | Contracts V1 5.7 | Infrastructure, Runtime, HTTP | Compatible |
| Mapper | Application Owner Source | owners métier cibles | Compatible |
| Repository PostgreSQL | Application + mapper + PDO | Provider, Runtime, delivery | Compatible |
| Migration 084 | schéma owner-scoped dédié | migrations gelées 5.1 à 5.6 | Additive |
| Streams | table partagée discriminée par stream | couplage inter-stream | Indépendants |

Discovery et Contracts 5.7 sont fermés et non modifiés. Les capacités 5.1 à 5.6 restent finales, fermées et gelées.
