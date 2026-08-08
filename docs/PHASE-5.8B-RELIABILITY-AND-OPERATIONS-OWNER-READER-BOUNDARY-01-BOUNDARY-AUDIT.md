# Phase 5.8B — Reliability & Operations — Owner Reader Boundary Audit

## Frontière qualifiée

L'owner retenu est `ReliabilityOperations`. La seule source candidate autorisée est `ReliabilityOperationsOwnerSource`.

La chaîne admissible est strictement : Owner Source → futur Owner Reader dédié → Reader public V1. Runtime, PostgreSQL, Mapper, Infrastructure et toute autre source sont exclus.

## Sept chaînes candidates

| Stream | Futur Owner Reader | Contrat public |
|---|---|---|
| Observability | ObservabilityOwnerReader | ObservabilityReaderV1 |
| ServiceHealth | ServiceHealthOwnerReader | ServiceHealthReaderV1 |
| Alerting | AlertingOwnerReader | AlertingReaderV1 |
| MaintenanceOperations | MaintenanceOperationsOwnerReader | MaintenanceOperationsReaderV1 |
| Continuity | ContinuityOwnerReader | ContinuityReaderV1 |
| CapacityPlanning | CapacityPlanningOwnerReader | CapacityPlanningReaderV1 |
| OperationalReadiness | OperationalReadinessOwnerReader | OperationalReadinessReaderV1 |

## Réduction candidate

Pour un `Found`, la décision fermée du `RevisionState` peut être recopiée homonymement vers le Status V1 du stream. `DependencyUnavailable` peut être propagé homonymement pour les sept chaînes.

La réduction exhaustive n'est toutefois pas actuellement matérialisable :

- `ObservabilityStatusV1` accepte `Missing`, mais pas `Corrupted` ;
- les six autres catalogues publics n'acceptent ni `Missing` ni `Corrupted` ;
- transformer `Missing` ou `Corrupted` en un autre statut créerait un fallback ou une décision nouvelle.

## Conclusion

La frontière, la source et les chaînes sont qualifiées. L'Owner Reader Foundation reste **NON AUTORISÉE** tant qu'une décision distincte n'a pas résolu la compatibilité exhaustive des catalogues, sans fallback, agrégation ni interprétation métier.
