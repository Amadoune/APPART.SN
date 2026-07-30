# Phase 4.7 — Administrative Action Lifecycle Atomic Event Matrix

| Transition inspectée | Événement 4.7E | Owner Outbox |
|---|---|---|
| `Draft → Record → Recorded` | `administrative.action.lifecycle.recorded` | `AdministrationAudit` |
| `Draft → Record → PendingApproval` | `administrative.action.lifecycle.approval_requested` | `AdministrationAudit` |
| `PendingApproval → Approve → Approved` | `administrative.action.lifecycle.approved` | `AdministrationAudit` |
| `PendingApproval → Reject → Rejected` | `administrative.action.lifecycle.rejected` | `AdministrationAudit` |

Chaque transition `Applied` produit exactement un événement et un message. Un
rejeu `AlreadyApplied` restaure la même transition et converge vers le même
`eventId` et le même `messageId`, sans nouvelle ligne.

| Résultat orchestration | Inspection | Outbox |
|---|---:|---:|
| `Applied` | obligatoire | append idempotent |
| `AlreadyApplied` | obligatoire | rejeu idempotent |
| `Missing` | interdite | aucune écriture |
| `VersionConflict` | interdite | aucune écriture |
| `Denied` | interdite | aucune écriture |
| `StateConflict` | interdite | aucune écriture |
| `ContextDivergence` | interdite | aucune écriture |
| `TransitionDivergence` | interdite | aucune écriture |
| `PersistenceCorrupted` | fermée | rollback |
