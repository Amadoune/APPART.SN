# Property Lifecycle Persistence Result Matrix

| Situation | Résultat |
|---|---|
| nouvelle initialisation ou append continu | `Applied` |
| même version et même checksum | `AlreadyApplied` |
| journal absent pour un append | `RejectedVersion` |
| trou ou recul de version | `RejectedVersion` |
| même version avec contenu différent | `StateConflict` |
| état source différent de l'état courant | `StateConflict` |
| triplet refusé par PostgreSQL | `TransitionRejected` |

La lecture retourne `Found`, `Missing` ou `Corrupted`. Un résultat corrompu n'expose jamais de snapshot exploitable.
