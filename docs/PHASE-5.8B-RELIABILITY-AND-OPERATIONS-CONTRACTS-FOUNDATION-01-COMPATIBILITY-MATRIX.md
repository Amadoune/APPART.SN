# Phase 5.8B — Reliability & Operations — Contracts Compatibility Matrix

| Garantie Discovery | Matérialisation Contracts | État |
|---|---|---|
| owner unique ReliabilityOperations | namespace contractuel unique | conforme |
| observabilité minimale | ObservabilityReaderV1 | conforme |
| health checks techniques | ServiceHealthReaderV1 | conforme |
| alerting public borné | AlertingReaderV1 | conforme |
| backup, restore et disaster recovery | ContinuityReaderV1 sans détail | conforme |
| maintenance, housekeeping et queues | MaintenanceOperationsReaderV1 sans commande | conforme |
| capacity planning | CapacityPlanningReaderV1 sans métrique | conforme |
| operational readiness | OperationalReadinessReaderV1 sans décision métier | conforme |
| données publiques minimales | status et observedAt uniquement | conforme |
| fail-closed | DependencyUnavailable dans chaque catalogue | conforme |
| aucune surface technique aval | aucune Persistence, Runtime ou delivery | conforme |
| Phase 5.8A gelée | aucune dépendance SecurityCompliance | conforme |
