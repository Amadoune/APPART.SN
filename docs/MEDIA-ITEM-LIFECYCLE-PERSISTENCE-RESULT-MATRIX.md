# Media Item Lifecycle Persistence Result Matrix

Lecture : `Found`, `Missing`, `Corrupted`.

Écriture :

| Résultat | Condition |
|---|---|
| `Applied` | append nouveau et continu |
| `AlreadyApplied` | même identité, version et checksum |
| `RejectedVersion` | absence ou version discontinue |
| `StateConflict` | état ou append divergent à version égale |
| `TransitionRejected` | contrainte PostgreSQL hors matrice certifiée |
