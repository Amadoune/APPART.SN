# Property Lifecycle Workflow Decision Table

| Entrée | Résultat | Transition | Diagnostic |
|---|---|---|---|
| paire présente dans la matrice | `Allowed` | source, cible et action exactes | aucun |
| action inconnue | `Denied` | aucune | `UnknownAction` |
| état terminal | `Denied` | aucune | `TerminalState` |
| cible nominale identique à l'état | `Denied` | aucune | `IncompatibleState` |
| autre paire | `Denied` | aucune | `TransitionForbidden` |

À état et action identiques, les trois colonnes de sortie sont strictement identiques. Le workflow ne possède aucune horloge, aucun UUID et aucune entrée cachée.
