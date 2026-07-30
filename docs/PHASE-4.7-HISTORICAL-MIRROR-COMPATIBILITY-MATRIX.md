# Phase 4.7B-R2 — Historical Mirror Compatibility Matrix

| Transition | Mutation historique obligatoire | Version après commit |
|---|---|---|
| Draft → Recorded | status, lastChangedAt, version, audit entry Record | expected + 1 |
| Draft → PendingApproval | status, lastChangedAt, version, audit entry Record | expected + 1 |
| PendingApproval → Approved | status, approval, decision, audit entry Approve, version | expected + 1 |
| PendingApproval → Rejected | status, decision, audit entry Reject, version | expected + 1 |

La transition exacte provient du Workflow. Le futur mapper la matérialise mécaniquement ; il ne décide ni état, ni action, ni autorité.
