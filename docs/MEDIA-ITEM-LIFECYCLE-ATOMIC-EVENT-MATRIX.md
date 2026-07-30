# Media Item Lifecycle Atomic Event Matrix

| Transition inspectée | Événement | Owner Outbox |
|---|---|---|
| `Active → Remove → Removed` | `media.item.lifecycle.removed` | `media` |
| `Active → Archive → Archived` | `media.item.lifecycle.archived` | `media` |

| Résultat orchestration | Émission |
|---|---|
| `Applied` | message créé ou déjà présent |
| `AlreadyApplied` | message déjà présent, aucune duplication |
| `Missing` | aucune |
| `VersionConflict` | aucune |
| `Denied` | aucune |
| `StateConflict` | aucune |
| `ContextDivergence` | aucune |
| `PersistenceCorrupted` | rollback complet |
