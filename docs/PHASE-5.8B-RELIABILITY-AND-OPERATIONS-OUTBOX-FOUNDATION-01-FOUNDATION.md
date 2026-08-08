# Phase 5.8B — Reliability & Operations — Outbox Foundation

## Périmètre

L'Outbox owner-scoped ReliabilityOperations est alimentée exclusivement par les sept Deliveries V1 certifiées : Observability, ServiceHealth, Alerting, MaintenanceOperations, Continuity, CapacityPlanning et OperationalReadiness.

Elle matérialise Writer, Reader, Store, Policy transactionnelle, Message, MessageId, EventId, MessageType, MessageStatus, résultats typés, mapper canonique, repository PostgreSQL unique et migration additive 089 avec rollback.

## Garanties

Le journal est append-only. L'identité du message et de l'événement est déterministe, le checksum SHA-256 est canonique, l'idempotence distingue Applied, AlreadyApplied et DivergentMessage. La lecture est ordonnée. Les claims concurrents utilisent FOR UPDATE SKIP LOCKED. Le retry est borné à dix tentatives.

Les opérations locales utilisent des savepoints et préservent le rollback externe. Aucun Transport, Routing ou Consumer n'est ouvert.

