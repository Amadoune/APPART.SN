# Place Lifecycle Runtime Orchestration — Matrice des résultats

| Observation | Classification | Workflow | Persistance | Résultat |
|---|---|---|---|---|
| `Corrupted` | non appelée | non appelé | non appelée | `InspectionCorrupted` |
| `Missing` | non appelée | refus | non appelée | `WorkflowRefused` + décision |
| `Missing` | non appelée | transition | `Applied` | `Applied` |
| `Missing` | non appelée | transition | `AlreadyApplied` | `AlreadyApplied` |
| `Missing` | non appelée | transition | `SourceVersionConflict` | `SourceVersionConflict` |
| `Missing` | non appelée | transition | `TargetVersionConflict` | `TargetVersionConflict` |
| `Missing` | non appelée | transition | `StateConflict` | `StateConflict` |
| `Missing` | non appelée | transition | `TransitionRejected` | `TransitionRejected` |
| `Found` | `ContextDivergence` | non appelé | non appelée | `ContextDivergence` |
| `Found` | `Conflict` | non appelé | non appelée | `ReplayConflict` |
| `Found` | `InspectionMissing` | non appelé | non appelée | `InspectionMissing` |
| `Found` | `InspectionCorrupted` | non appelé | non appelée | `InspectionCorrupted` |
| `Found` | candidat `AlreadyApplied` | refus | non appelée | `WorkflowRefused` + décision |
| `Found` | candidat `AlreadyApplied` | transition | `AlreadyApplied` | `AlreadyApplied` |
| `Found` | candidat `AlreadyApplied` | transition | `SourceVersionConflict` | `ReplayConflict` |
| `Found` | candidat `AlreadyApplied` | transition | autre résultat | résultat propriétaire |

Chaque ligne est terminale. Aucune branche ouverte ni résultat par défaut
n'existe.
