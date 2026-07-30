# Professional Status Persistence Result Matrix

## Lecture

| Résultat | Signification |
|---|---|
| `Found` | snapshot courant valide |
| `Missing` | aucune initialisation |
| `Corrupted` | ligne illisible ou checksum divergent |

## Écriture

| Résultat | Signification |
|---|---|
| `Applied` | ligne ajoutée |
| `AlreadyApplied` | rejeu byte-for-byte identique |
| `RejectedVersion` | absence, saut ou version obsolète |
| `StateConflict` | état source ou écriture de même version divergents |
| `TransitionRejected` | transition hors matrice rejetée par PostgreSQL |
