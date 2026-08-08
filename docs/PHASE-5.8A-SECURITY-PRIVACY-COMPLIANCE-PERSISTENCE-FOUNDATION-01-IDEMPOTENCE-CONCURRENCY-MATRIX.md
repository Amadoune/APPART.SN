# Idempotence and Concurrency Matrix

| Situation | Résultat |
|---|---|
| nouvelle révision séquentielle | `Applied` |
| même clé, stream, révision et checksum | `AlreadyApplied` |
| même clé, stream et révision, checksum différent | `DivergentRevision` |
| révision non séquentielle ou chronologie non monotone | `VersionConflict` |
| donnée invalide | `Corrupted` |
| dépendance PostgreSQL indisponible | `DependencyUnavailable` |

La concurrence est sérialisée par advisory lock sur la paire sujet/stream, `FOR UPDATE`, clé primaire et `ON CONFLICT`. Un savepoint local protège toute transaction appelante et son rollback externe.
