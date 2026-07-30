# Stratégie de propagation multi-cibles

`MultiTargetPropagationStrategy::plan(request, checkpoint, limit)` renvoie une page fermée :

| Résultat | Cibles | Suite |
|---|---:|---|
| `TargetsAvailable` | 1..limit | reprendre avec le checkpoint |
| `NoTargets` | 0 | terminer sans effet |
| `Completed` | 1..limit | traiter puis terminer |
| `InvalidIdentity` | 0 | diagnostic explicite |
| `Corrupted` | 0 | diagnostic explicite |

La requête conserve l'identité source (`Property` ou `Media`) et exige une Property normalisée
explicite. Aucune convention ne permet de déduire la Property depuis un identifiant Media.

Règle future de consommation : traiter les cibles dans l'ordre fourni, ne valider le message source
qu'après `Completed`, et laisser toute interruption produire une redelivery complète et idempotente.
