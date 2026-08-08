# Phase 5.8B — Reliability & Operations — Outbox Matrix

| Delivery V1 | Message Type V1 | Propagation |
|---|---|---|
| ObservabilityDeliveryV1 | reliability-operations.observability.observed.v1 | type, status, observedAt |
| ServiceHealthDeliveryV1 | reliability-operations.service-health.observed.v1 | type, status, observedAt |
| AlertingDeliveryV1 | reliability-operations.alerting.observed.v1 | type, status, observedAt |
| MaintenanceOperationsDeliveryV1 | reliability-operations.maintenance-operations.observed.v1 | type, status, observedAt |
| ContinuityDeliveryV1 | reliability-operations.continuity.observed.v1 | type, status, observedAt |
| CapacityPlanningDeliveryV1 | reliability-operations.capacity-planning.observed.v1 | type, status, observedAt |
| OperationalReadinessDeliveryV1 | reliability-operations.operational-readiness.observed.v1 | type, status, observedAt |

| Opération | Résultats fermés |
|---|---|
| append | Applied, AlreadyApplied, DivergentMessage, DependencyUnavailable |
| claim | Claimed, AlreadyClaimed, AlreadyCompleted, AttemptsExhausted, Missing, Corrupted, DependencyUnavailable |
| retry | RetryScheduled, AttemptsExhausted, AlreadyCompleted, Missing, Corrupted, DependencyUnavailable |

