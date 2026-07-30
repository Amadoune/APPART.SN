# Lead Lifecycle Persistence Result Matrix

## Lecture

| Statut | Signification |
|---|---|
| `Found` | snapshot courant restauré et intègre |
| `Missing` | aucune ligne pour le Lead |
| `Corrupted` | ligne courante non reconstructible ou checksum divergent |

## Écriture

| Résultat | Condition |
|---|---|
| `Applied` | initialisation ou append effectivement inséré |
| `AlreadyApplied` | même version et même checksum déjà présents |
| `RejectedVersion` | flux absent pour un append ou version non continue |
| `StateConflict` | état courant ou contenu de version incompatible |
| `TransitionRejected` | contrainte PostgreSQL refusant une transition non certifiée |

Aucune exception technique n'est convertie en décision métier. Seul le refus d'intégrité `23514` est classé comme `TransitionRejected`.
