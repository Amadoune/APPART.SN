# Professional Status Orchestration Result Matrix

| Situation | Résultat |
|---|---|
| transition persistée | `Applied` |
| rejeu exact | `AlreadyApplied` |
| professionnel absent | `Missing` |
| version incompatible ou inspection manquante | `VersionConflict` |
| refus du workflow | `Denied` avec diagnostic exact |
| conflit d'état, transition refusée ou action de rejeu différente | `StateConflict` |
| même append avec acteur ou instant différent | `ContextDivergence` |
| lecture, inspection ou persistance corrompue | `PersistenceCorrupted` |
