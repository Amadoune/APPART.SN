# Phase 4.7B-R1 — Persistence Authority Matrix

| Opération | Autorité | Écriture | Fallback |
|---|---|---|---|
| Creation | HistoricalRegistry | HistoricalOnly | interdit |
| ReasonMutation | HistoricalRegistry | HistoricalOnly | interdit |
| AuditDetailMutation | HistoricalRegistry | HistoricalOnly | interdit |
| LifecycleEnrollment | LifecycleJournal | LifecycleOnly depuis checkpoint exact | interdit |
| LifecycleRead | LifecycleJournal | ReadOnly | interdit |
| LifecycleTransition | LifecycleJournal | AtomicHistoricalAndLifecycle | interdit |
| CompatibilityRead | HistoricalRegistry | ReadOnly | interdit |

Il n'existe jamais deux autorités pour une même opération.
