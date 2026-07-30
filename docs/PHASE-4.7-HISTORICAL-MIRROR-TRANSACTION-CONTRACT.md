# Phase 4.7B-R2 — Historical Mirror Transaction Contract

Le port `AdministrativeActionLifecycleAtomicPersistenceTransaction` expose une exécution atomique avec un mode explicite :

- `Local` : le composant possède begin/commit/rollback ;
- `External` : le composant réutilise la transaction appelante et ne commit ni ne rollback à sa place.

Les résultats fermés couvrent :

- `Applied` ;
- `AlreadyApplied` ;
- `VersionConflict` ;
- `StateConflict` ;
- `MirrorDivergence` ;
- `EnrollmentDivergence` ;
- `Corrupted` ;
- `PersistenceCorrupted`.

Aucune exception technique ne doit franchir le futur port de persistance.
