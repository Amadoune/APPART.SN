# Risk Register — Legacy Migration Persistence

| Risque | Impact | Maîtrise | Résiduel |
|---|---|---|---|
| transfert implicite d'autorité métier | critique | owner limité à la coordination, états fermés | faible |
| mélange des cinq streams | élevé | discriminant, contraintes et advisory lock par stream | faible |
| divergence silencieuse d'une révision | élevé | checksum SHA-256 canonique + `DivergentRevision` | faible |
| perte d'une transaction appelante | élevé | savepoints locaux, commit/rollback conditionnels | faible |
| lecture temporelle anachronique | élevé | double borne effective/recorded et ordre déterministe | faible |
| altération d'une capacité gelée | critique | migration 084 additive dans un schéma dédié | faible |
| contention volumétrique future | moyen | index temporel et verrou à granularité stream/sujet | à mesurer |

Aucun risque n'autorise l'ouverture implicite d'un Runtime ou d'une surface de delivery.
