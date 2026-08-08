# Outbox Matrix

| Source Delivery | Message Type |
|---|---|
| SecretInventoryDeliveryV1 | security-compliance.secret-inventory.observed.v1 |
| SecurityAuditDeliveryV1 | security-compliance.security-audit.observed.v1 |
| IncidentDeliveryV1 | security-compliance.incident.observed.v1 |
| PrivacyPolicyDeliveryV1 | security-compliance.privacy-policy.observed.v1 |
| ComplianceControlDeliveryV1 | security-compliance.compliance-control.observed.v1 |

| Opération | Résultats fermés |
|---|---|
| Append | Applied, AlreadyApplied, DivergentMessage, DependencyUnavailable |
| Claim | Claimed, AlreadyClaimed, AlreadyCompleted, AttemptsExhausted, Missing, Corrupted, DependencyUnavailable |
| Retry | RetryScheduled, AttemptsExhausted, AlreadyCompleted, Missing, Corrupted, DependencyUnavailable |

Le payload métier contient exclusivement type, status et observedAt. La lecture est owner-scoped, bornée à 100 et ordonnée par availableAt, createdAt puis messageId.
