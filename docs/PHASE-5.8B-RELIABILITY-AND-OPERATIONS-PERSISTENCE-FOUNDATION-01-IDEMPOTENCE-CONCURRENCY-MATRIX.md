# Phase 5.8B — Reliability & Operations — Idempotence & Concurrency Matrix

| Cas | Résultat |
|---|---|
| première révision 1 | Applied |
| même scope, stream, révision et checksum | AlreadyApplied |
| même révision, checksum différent | DivergentRevision |
| saut ou révision non suivante | VersionConflict |
| chronologie non monotone | VersionConflict |
| panne PostgreSQL | DependencyUnavailable |
| donnée interne invalide | Corrupted |

Chaque append prend un `pg_advisory_xact_lock` déterministe par couple scope/stream avant la lecture courante. Les transactions externes restent propriétaires de leur commit ; le repository utilise un savepoint local lorsqu'une transaction est déjà ouverte.
