# Risk Register — Legacy Migration Outbox

| Risque | Impact | Maîtrise | Résiduel |
|---|---|---|---|
| duplication de publication | élevé | messageId déterministe et clé primaire | faible |
| divergence silencieuse | critique | JSON et checksum comparés sous `FOR UPDATE` | faible |
| ordre non déterministe | élevé | ordre composé `created_at,message_id` | faible |
| retry infini | élevé | contrainte et filtre à dix | faible |
| perte d'une transaction appelante | critique | savepoints et ownership transactionnel | faible |
| dérive UTC | moyen | reconstruction canonique explicite | faible |
| modification de migration 084 | critique | empreintes Architecture conservées | faible |
| ouverture implicite Transport | critique | absence de Provider, routing et consumer | faible |

Aucun risque résiduel n'autorise une Foundation ultérieure.
