# Notifications Persistence — Idempotence & Concurrency Matrix

| Situation | Décision |
|---|---|
| Première révision valide | `Applied` |
| Même révision, même checksum | `AlreadyApplied` |
| Même révision, checksum différent | `DivergentRevision` |
| Révision absente, saut ou chronologie invalide | `VersionConflict` |
| Écritures concurrentes | Advisory lock par clé et stream ; un gagnant |
| Transaction externe | Savepoint local, rollback laissé à l'appelant |
