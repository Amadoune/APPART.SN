# Phase 5.8B — Reliability & Operations — Owner Reader Dependency Matrix

| Dépendance | Autorisée | Condition |
|---|---:|---|
| ReliabilityOperationsOwnerSource | oui | unique source de lecture |
| ReliabilityOperationsScopeKey | oui | clé owner-scoped interne |
| ReliabilityOperationsStream | oui | stream nominatif exact |
| ReliabilityOperationsObservedAt | oui | instant UTC canonique |
| ReadResult et RevisionState | oui | réduction mécanique uniquement |
| Status et Result V1 dédiés | oui | sortie publique minimale |
| Runtime et diagnostics | non | disponibilité technique hors frontière |
| repository PostgreSQL | non | Infrastructure interdite |
| Mapper | non | Persistence interdite |
| SQL et migration 088 | non | implémentation interdite |
| métriques, logs, traces, alertes | non | aucune source parallèle |
| autre owner ou capacité gelée | non | aucune autorité transverse |
| Provider et bindings | non | hors Boundary Audit |
