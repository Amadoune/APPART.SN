# Phase 5.8B — Reliability & Operations — Persistence Foundation

## Owner Source

`ReliabilityOperationsOwnerSource` est le port owner-scoped unique. Il conserve sept streams indépendants : Observability, ServiceHealth, Alerting, Continuity, MaintenanceOperations, CapacityPlanning et OperationalReadiness.

La Persistence contient un `RevisionState`, un `ReadResult`, un `WriteResult`, un mapper bidirectionnel et `PostgreSqlReliabilityOperationsOwnerSource`.

## Stockage

La migration additive 088 crée le schéma `reliability_operations`, un journal append-only et un index courant dérivé. Le rollback 088 supprime exclusivement ces objets.

## Garanties

- journal append-only owner-scoped ;
- lectures temporelles déterministes ;
- révisions monotones et optimistic locking ;
- checksum SHA-256 canonique ;
- idempotence et divergence explicites ;
- advisory lock transactionnel par scope et stream ;
- savepoints locaux et rollback externe préservé ;
- aucune Foundation Runtime ou surface aval ouverte.
