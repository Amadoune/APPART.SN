# Phase 4.7A — Administrative Action State Machine

```text
Draft
 ├─ Record + Present + DirectRecording ───────────────► Recorded
 └─ Record + Present + IndependentApprovalRequired ──► PendingApproval
                                                         ├─ Approve + Present ─► Approved
                                                         └─ Reject + Present ──► Rejected
```

`Recorded`, `Approved` et `Rejected` sont terminaux. La création, l'ajout du motif et l'exécution de la ressource cible sont hors workflow.
