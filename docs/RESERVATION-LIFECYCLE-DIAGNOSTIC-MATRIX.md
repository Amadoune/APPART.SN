# Reservation Lifecycle Diagnostic Matrix

| Diagnostic | Condition | Priorité |
|---|---|---:|
| `UnknownAction` | action `Unknown` | 1 |
| `TerminalState` | action connue sur `Completed`, `Cancelled`, `Expired` ou `Rejected` | 2 |
| `IncompatibleState` | l'action vise nominalement l'état courant | 3 |
| `TransitionForbidden` | couple connu absent de la matrice | 4 |
| `WorkflowCorrupted` | réserve contractuelle pour une incohérence construite | explicite uniquement |

Les diagnostics sont des valeurs métier fermées. Ils ne transportent ni exception ni détail technique.
