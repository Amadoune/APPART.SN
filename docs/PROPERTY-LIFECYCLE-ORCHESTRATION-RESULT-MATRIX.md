# Property Lifecycle Orchestration Result Matrix

| Source | Résultat source | Résultat d'orchestration | Diagnostic |
|---|---|---|---|
| workflow | `Denied` | `Denied` | diagnostic workflow exact |
| store append | `Applied` | `Applied` | aucun |
| store append | `AlreadyApplied` | `AlreadyApplied` | aucun |
| store append | `RejectedVersion` | `ConcurrencyConflict` | `version_conflict` |
| store append | `StateConflict` | `ConcurrencyConflict` | `state_conflict` |
| store append | `TransitionRejected` | `PersistenceFailure` | `transition_rejected` |
| store read | `Missing` | `PersistenceFailure` | `current_state_missing` |
| store read | `Corrupted` | `PersistenceFailure` | `stored_state_corrupted` |
| infrastructure | exception | `PersistenceFailure` | `infrastructure_failure` |

Les résultats sont fermés à `Applied`, `AlreadyApplied`, `Denied`, `ConcurrencyConflict` et `PersistenceFailure`.
