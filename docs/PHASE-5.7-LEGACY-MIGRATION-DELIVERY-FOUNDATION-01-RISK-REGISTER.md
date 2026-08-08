# Risk Register — Legacy Migration Delivery

| Risque | Impact | Maîtrise | Résiduel |
|---|---|---|---|
| perte ou transformation du type Event | élevé | type conservé directement hors Payload | faible |
| divergence de statut | élevé | conversion stricte homonyme et 27 cas testés | faible |
| transformation de `observedAt` | élevé | recopie directe depuis le Payload Event | faible |
| ajout de données internes | critique | Payload typé à deux champs | faible |
| agrégation de streams | élevé | cinq Factories indépendantes | faible |
| dépendance Reader ou Infrastructure | critique | garde Architecture | faible |
| ouverture implicite d'une Outbox | critique | interdiction structurelle documentée et testée | faible |

Aucun risque résiduel n'autorise un Provider, un binding ou une Foundation ultérieure.
