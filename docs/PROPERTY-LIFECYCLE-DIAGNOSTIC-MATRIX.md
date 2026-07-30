# Property Lifecycle Diagnostic Matrix

| Condition | Diagnostic | Priorité |
|---|---|---:|
| action `Unknown` | `unknown_action` | 1 |
| état `Archived` avec action connue | `terminal_state` | 2 |
| action dont la cible nominale est déjà l'état courant | `incompatible_state` | 3 |
| autre paire absente de la matrice | `transition_forbidden` | 4 |
| incohérence contractuelle construite par une future frontière | `workflow_corrupted` | contractuelle |

La priorité rend chaque refus unique et reproductible. `workflow_corrupted` appartient au vocabulaire fermé sans être synthétisé par des entrées enum valides.
