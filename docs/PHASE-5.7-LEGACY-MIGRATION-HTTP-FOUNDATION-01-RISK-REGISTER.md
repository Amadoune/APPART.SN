# Risk Register — Legacy Migration HTTP

| Risque | Impact | Maîtrise | Résiduel |
|---|---|---|---|
| champ inconnu accepté | élevé | validation post-règles sur toutes les queries | faible |
| mapping HTTP incomplet | élevé | match exhaustif et tests des 27 états | faible |
| logique métier dans un Controller | critique | délégation directe Reader → ResponseFactory | faible |
| accès direct à l'Owner Source | critique | garde Architecture sur cinq Reader V1 | faible |
| fuite de données internes | critique | réponse limitée à statut et observedAt | faible |
| cache d'une observation opérationnelle | moyen | `Cache-Control: no-store` | faible |
| route ou Provider dupliqué | moyen | preuves d'unicité Architecture | faible |

Aucun risque résiduel n'autorise l'ouverture d'une surface Event ou ultérieure.
