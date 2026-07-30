# Phase 4.9A — Matrice des dépendances

| Fondation / capacité | Usage futur autorisé | Contrainte |
|---|---|---|
| `IdentityAccess.Account` historique | source d'observation et coexistence | aucun changement pendant 4.9A |
| `AccountRegistry` | référence pour l'audit de coexistence | contrat gelé, aucune extension implicite |
| Repository / Transaction foundations | conventions de concurrence et rollback | consommation additive uniquement |
| PostgreSQL foundation | future tranche propriétaire | aucune migration avant Persistence Foundation |
| Runtime Composition / Health | bindings paresseux et santé additive | aucun binding en Discovery |
| Event Contract / Transport / Routing | patrons versionnés | nouveaux contrats seulement après gates |
| Generic Delivery / Outbox | réutilisation future | audit owner et compatibilité avant écriture |
| Authentication / sessions | consommateur ou owner d'effet à définir | jamais une dépendance cachée du Workflow |
| Role Assignment | owner des rôles | pas de révocation implicite par le Workflow |
| Verification / Credential / Consent | sous-domaines voisins | état orthogonal, aucune mutation |
| Phase 4.8 Place Lifecycle | aucune dépendance métier | intégralement gelée |
| Migrations 038, 039, 040 | aucune | modification interdite |

Toute incompatibilité impose un amendement versionné et suspend le jalon
concerné.
