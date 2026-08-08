# Risk Register — Legacy Migration Owner Reader Boundary

| Risque | Impact | Maîtrise | Résiduel |
|---|---|---|---|
| interprétation métier d'un statut | critique | réduction homonyme stricte | faible |
| agrégation des cinq streams | élevé | cinq Readers indépendants | faible |
| fallback masquant une panne | élevé | catalogue exhaustif sans fallback | faible |
| exposition d'un Revision State | élevé | résultat V1 limité au statut et à `observedAt` | faible |
| fuite de donnée Legacy ou PII | critique | aucune donnée source dans la réduction | faible |
| dépendance directe à PostgreSQL | élevé | port Application comme source unique | faible |
| transfert d'autorité aux coordinateurs | critique | owners cibles explicitement souverains | faible |

Aucun risque résiduel n'autorise une implémentation dans ce jalon documentaire.
