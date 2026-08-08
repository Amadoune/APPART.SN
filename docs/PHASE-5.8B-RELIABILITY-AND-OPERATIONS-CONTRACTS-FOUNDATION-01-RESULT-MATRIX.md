# Phase 5.8B — Reliability & Operations — Result Matrix

| Reader | Catalogue fermé |
|---|---|
| ObservabilityReaderV1 | Available, Degraded, Missing, DependencyUnavailable |
| ServiceHealthReaderV1 | Healthy, Degraded, Unavailable, DependencyUnavailable |
| AlertingReaderV1 | Ready, Degraded, Unavailable, DependencyUnavailable |
| ContinuityReaderV1 | Ready, AtRisk, Blocked, DependencyUnavailable |
| MaintenanceOperationsReaderV1 | Ready, Degraded, Blocked, DependencyUnavailable |
| CapacityPlanningReaderV1 | Sufficient, AtRisk, Exhausted, DependencyUnavailable |
| OperationalReadinessReaderV1 | Ready, AtRisk, Blocked, DependencyUnavailable |

Chaque Result V1 expose exactement son Status V1 et `observedAt` UTC canonique. Les catalogues sont exhaustifs dans leur version V1, sans fallback et sans valeur ouverte.
