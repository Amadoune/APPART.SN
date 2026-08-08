# Risk Register — Legacy Migration Owner Reader Foundation

| Risque | Impact | Maîtrise | Résiduel |
|---|---|---|---|
| réduction incomplète | élevé | tests exhaustifs sur 27 états contextuels | faible |
| fallback ou interprétation | critique | conversion stricte `from`, aucun défaut | faible |
| agrégation des streams | élevé | cinq Readers indépendants | faible |
| calcul implicite de readiness | critique | propagation homonyme seulement | faible |
| fuite d'un Revision State ou de PII | critique | construction exclusive des Results V1 | faible |
| dépendance Infrastructure | élevé | garde Architecture | faible |
| alias public ambigu | élevé | cinq aliases nominatifs uniques | faible |
| dérive de migration 084 | critique | empreintes Architecture inchangées | faible |

Aucun risque résiduel n'autorise l'ouverture d'une Foundation ultérieure.
