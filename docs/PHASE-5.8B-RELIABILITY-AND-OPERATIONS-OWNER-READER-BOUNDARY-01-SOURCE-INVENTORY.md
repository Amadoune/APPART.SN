# Phase 5.8B — Reliability & Operations — Source Inventory

## Source candidate unique

`ReliabilityOperationsOwnerSource`

Le port Application offre une lecture owner-scoped par `ReliabilityOperationsScopeKey`, `ReliabilityOperationsStream` et `ReliabilityOperationsObservedAt`. Son `ReadResult` fermé expose `Found`, `Missing`, `Corrupted` et `DependencyUnavailable`.

## Sources rejetées

| Source | Motif d'exclusion |
|---|---|
| ReliabilityOperationsRuntimeV1 | disponibilité technique seulement |
| PostgreSqlReliabilityOperationsOwnerSource concret | Infrastructure interdite à la frontière |
| ReliabilityOperationsOwnerSourceMapper | détail de Persistence |
| tables et vues PostgreSQL | accès direct interdit |
| métriques, logs et traces | données opérationnelles, pas source owner-scoped |
| Providers et container Laravel | composition, pas autorité |
| toute capacité gelée | cross-owner interdit |

Aucune source de remplacement, source secondaire ou stratégie de fallback n'est admissible.
