# Idempotence & Concurrency Matrix

| Cas | Résultat |
|---|---|
| nouvelle révision exactement suivante | Applied |
| même révision, même checksum | AlreadyApplied |
| même révision, checksum différent | DivergentRevision |
| saut ou ordre temporel invalide | VersionConflict |
| écritures concurrentes | advisory lock par subject et stream, un seul Applied |
| transaction externe | savepoint local ; commit/rollback laissé à l'appelant |

Le checksum SHA-256 couvre la représentation canonique complète de la révision.
