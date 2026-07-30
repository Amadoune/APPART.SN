# Phase 4.7E — Event Contract Analysis

Le catalogue expose exactement quatre faits bijectifs :

| Transition | Événement |
|---|---|
| `Draft → Record → Recorded` | `administrative.action.lifecycle.recorded` |
| `Draft → Record → PendingApproval` | `administrative.action.lifecycle.approval_requested` |
| `PendingApproval → Approve → Approved` | `administrative.action.lifecycle.approved` |
| `PendingApproval → Reject → Rejected` | `administrative.action.lifecycle.rejected` |

Toute autre transition est refusée explicitement.
