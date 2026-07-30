# Professional Status Diagnostic Matrix

| Diagnostic | Condition |
|---|---|
| `IncompatibleState` | action connue redondante ou incompatible avec l'état courant |
| `UnknownAction` | action sentinelle `Unknown`, quel que soit l'état |

`UnknownAction` est prioritaire dès que l'action est inconnue. Aucun diagnostic ne contient d'information technique.
