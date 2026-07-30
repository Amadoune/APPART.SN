# Place Lifecycle Persistence Result Matrix

| Situation durable | Résultat fermé | Écriture |
|---|---|---:|
| enrôlement absent et valide | `Applied` | une ligne |
| enrôlement identique répété | `AlreadyApplied` | aucune |
| transition nouvelle, versions exactes | `Applied` | une ligne |
| transition identique répétée | `AlreadyApplied` | aucune |
| source absente ou version divergente | `SourceVersionConflict` | aucune |
| cible absente ou version divergente | `TargetVersionConflict` | aucune |
| état source différent de la transition | `StateConflict` | aucune |
| contrainte de transition refusée | `TransitionRejected` | aucune |
| lecture absente | `Missing` | aucune |
| checksum ou mapping invalide | `Corrupted` | aucune |

La matrice ne contient aucune décision appartenant au Workflow ou à
l'Orchestration.
