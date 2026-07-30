# Listing Publication Orchestration Result Matrix

| Situation | Résultat | Diagnostic |
|---|---|---|
| Décision autorisée et écriture appliquée | `Applied` | Aucun |
| Écriture idempotente déjà appliquée | `AlreadyApplied` | Aucun |
| Décision refusée | `Denied` | Diagnostic exact du workflow |
| Version attendue différente | `ConcurrencyConflict` | `VersionConflict` |
| Store : `RejectedVersion` | `ConcurrencyConflict` | `VersionConflict` |
| Store : `StateConflict` | `ConcurrencyConflict` | `StateConflict` |
| Store : `TransitionRejected` | `PersistenceFailure` | `TransitionRejected` |
| État absent | `PersistenceFailure` | `CurrentStateMissing` |
| État corrompu | `PersistenceFailure` | `StoredStateCorrupted` |
| Exception de lecture ou d'écriture | `PersistenceFailure` | `InfrastructureFailure` |

Aucun autre résultat et aucune branche implicite ne sont admis.
