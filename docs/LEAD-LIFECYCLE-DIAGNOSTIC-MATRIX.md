# Lead Lifecycle Diagnostic Matrix

## Diagnostics fermés et priorité

| Priorité | Diagnostic | Condition |
|---:|---|---|
| 1 | `UnknownAction` | action `Unknown`, quel que soit l'état |
| 2 | `TerminalState` | action reconnue demandée depuis `Closed` |
| 3 | `IncompatibleState` | `Deliver` ou `Reject` après la sortie de `Created` |
| 4 | `TransitionForbidden` | autre couple reconnu absent de la matrice (`Created` + `Close`) |

La priorité rend notamment `Closed + Unknown` observable comme `UnknownAction`. Aucun diagnostic ne contient d'information technique.
