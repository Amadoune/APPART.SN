# Matrice commit / rollback

| Étape | Outcome | Décision transaction Listing | Résultat appelant |
|---|---|---|---|
| Public facts | `Applied` | poursuivre | aucun résultat terminal |
| Public facts | `AlreadyApplied` | poursuivre | aucun résultat terminal |
| Public facts | tout autre résultat existant | rollback | réduction existante/indisponibilité |
| Workflow | `Applied` + transition | commit | `AuthoringOperationStatus::Applied` |
| Workflow | `AlreadyApplied` + transition | commit | `AuthoringOperationStatus::AlreadyApplied` |
| Workflow | `Denied` | rollback | `LifecycleConflict` |
| Workflow | `ConcurrencyConflict` | rollback | `ConcurrentModification` |
| Workflow | `PersistenceFailure` | rollback | `DependencyUnavailable` |
| Toute étape | exception technique | rollback | réduction technique existante |

Il n’existe aucun outcome `UNKNOWN`. Un succès sans transition est incohérent et doit suivre la voie rollback/indisponibilité déjà fermée.
