# Phase 4.7D — Orchestration Result Matrix

| Source | Résultat orchestration |
|---|---|
| append appliqué | `Applied` |
| rejeu exact | `AlreadyApplied` |
| action non enrôlée ou inspection absente | `Missing` |
| version incompatible | `VersionConflict` |
| décision Workflow refusée | `Denied` + diagnostic exact |
| conflit d'état | `StateConflict` |
| contexte divergent | `ContextDivergence` |
| transition divergente | `TransitionDivergence` |
| lecture, enrôlement ou persistance corrompus | `PersistenceCorrupted` |

Aucune exception métier non typée n'est produite.
