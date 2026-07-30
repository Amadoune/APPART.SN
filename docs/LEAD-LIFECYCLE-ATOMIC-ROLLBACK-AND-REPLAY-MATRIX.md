# Lead Lifecycle Atomic Rollback and Replay Matrix

| Résultat orchestration / Outbox | Journal/contextes | Outbox | Résultat |
|---|---|---|---|
| `Applied` + Outbox `Applied` | commit | commit | `Applied` |
| `AlreadyApplied` + Outbox `AlreadyApplied` | inchangé | inchangé | `AlreadyApplied` |
| `Denied`, `Missing`, conflit ou divergence | aucune nouvelle écriture | aucune écriture | résultat inchangé |
| persistance ou inspection corrompue | rollback | rollback | `PersistenceCorrupted` |
| Outbox refusée ou en échec | rollback intégral | rollback | `PersistenceCorrupted` |

Sous concurrence identique, une seule transition, un seul contexte et un seul message subsistent ; les appels convergent vers `Applied` et `AlreadyApplied`.
