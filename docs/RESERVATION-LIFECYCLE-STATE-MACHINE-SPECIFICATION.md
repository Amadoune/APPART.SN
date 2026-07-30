# Reservation Lifecycle State Machine Specification

| État | Rôle | Terminal |
|---|---|---:|
| `Draft` | réservation préparée mais non soumise | non |
| `Requested` | demande transmise et en attente de décision | non |
| `Confirmed` | demande acceptée avant son démarrage | non |
| `InProgress` | occupation ou prestation commencée | non |
| `Completed` | réservation exécutée jusqu'à son terme | oui |
| `Cancelled` | réservation interrompue volontairement | oui |
| `Expired` | demande ou confirmation devenue caduque | oui |
| `Rejected` | demande explicitement refusée | oui |

Actions fermées : `Submit`, `Confirm`, `Reject`, `Start`, `Complete`, `Cancel`, `Expire`, `Unknown`. `Unknown` est exclusivement diagnostique et ne possède aucune cible métier.

Décisions fermées : `Allowed` et `Denied`. Une décision autorisée contient exactement une transition et aucun diagnostic ; une décision refusée contient exactement un diagnostic et aucune transition.
