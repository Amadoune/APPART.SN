# Phase 5.8C — Experience & Acceptance — Idempotence & Concurrency Matrix

| Situation | Résultat |
|---|---|
| Nouvelle révision valide | Applied |
| Replay strictement identique | AlreadyApplied |
| Même identité, checksum différent | DivergentRevision |
| Révision non consécutive | VersionConflict |
| Chronologie non monotone | VersionConflict |
| État illisible | Corrupted |
| PostgreSQL indisponible | DependencyUnavailable |

Chaque stream prend un advisory lock transactionnel déterministe. Les opérations imbriquées utilisent un savepoint local ; aucune transaction externe n'est commitée implicitement.

