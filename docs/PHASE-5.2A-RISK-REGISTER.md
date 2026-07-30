# Phase 5.2A — Risk Register

| Risque | Niveau | Prévention / Gate |
|---|---|---|
| double autorité sur les statuts Property/Listing | critique | références seules ; aucun statut canonique dans Authoring |
| extension silencieuse de F-01/F-02 | critique | commande publique existante ou amendement préalable |
| ownership déduit d'un AccountId client | critique | auto-scope par session et lookup owner |
| takeover après changement de claim IAM | critique | ownership lié à l'AccountId stable, jamais à email/téléphone |
| transaction multi-domaines | critique | saga/intents locaux et compensations ; aucune FK cross-domain |
| publication de PII ou contenu privé | critique | événements minimisés et catalogue séparé |
| modification de F-11 pour afficher les nouveaux champs | critique | hors 5.2A ; amendement dédié si nécessaire |
| dépendance prématurée à 5.2B Media | élevé | complétude Media en lecture optionnelle, publication fail-closed |
| dépendance prématurée à 5.2C Professional | élevé | titulaire Account uniquement ; délégation locale minimale |
| brouillon perdu ou écrasé | élevé | optimistic locking, intent idempotent, historique append-only |
| portefeuille utilisé comme autorité | élevé | projection reconstruisible, commandes interdites |
| taxonomie non versionnée | élevé | IDs stables, version normative et compatibilité explicite |
| fuite de localisation privée | élevé | adresse précise confinée ; exposition publique hors périmètre |

## Amendements

Aucun amendement n'est ouvert par ce Discovery. Un amendement devient
obligatoire si les contrats futurs ne peuvent éviter une mutation de F-01,
F-02, F-11, F-14, F-15, F-17 ou F-18.
