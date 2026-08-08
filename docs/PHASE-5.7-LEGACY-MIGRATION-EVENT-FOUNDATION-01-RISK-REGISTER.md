# Risk Register — Legacy Migration Event

| Risque | Impact | Maîtrise | Résiduel |
|---|---|---|---|
| réduction non exhaustive | élevé | catalogues fermés et 27 cas testés | faible |
| transformation de `observedAt` | élevé | recopie directe depuis le Result et test avec instant distinct | faible |
| création de plusieurs Events | élevé | Factory unitaire et expectation Reader unique | faible |
| ajout de données métier ou PII | critique | Payload typé à deux champs | faible |
| agrégation des streams | élevé | cinq Factories indépendantes | faible |
| dépendance HTTP ou Infrastructure | critique | garde Architecture | faible |
| dérive d'un type V1 | élevé | cinq valeurs exactes testées | faible |

Aucun risque résiduel n'autorise un Provider, une Delivery ou une Outbox dans cette Foundation.
