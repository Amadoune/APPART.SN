# Professional Status Transition Matrix

| État courant | Action | Décision | État suivant / diagnostic |
|---|---|---|---|
| Active | Suspend | Allowed | Suspended |
| Active | Reactivate | Denied | IncompatibleState |
| Active | Unknown | Denied | UnknownAction |
| Suspended | Suspend | Denied | IncompatibleState |
| Suspended | Reactivate | Allowed | Active |
| Suspended | Unknown | Denied | UnknownAction |

Les six couples état/action sont classifiés explicitement.
