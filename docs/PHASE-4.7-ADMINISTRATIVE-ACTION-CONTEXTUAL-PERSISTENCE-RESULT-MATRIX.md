# Phase 4.7C-R2 — Contextual Persistence Result Matrix

| Situation | Résultat |
|---|---|
| journal, miroir et contexte écrits | `Applied` |
| transition et contexte strictement identiques | `AlreadyApplied` |
| transition exacte, contexte différent | `ContextDivergence` |
| version incompatible | `VersionConflict` |
| état incompatible | `StateConflict` |
| transition différente au même append | `TransitionDivergence` |
| enrôlement divergent | `EnrollmentDivergence` |
| donnée persistée invalide | `Corrupted` |
| défaillance technique de persistance | `PersistenceCorrupted` |

L'inspection restitue exclusivement `Found`, `Missing` ou `Corrupted`.
