# Reservation Lifecycle Orchestration Dependency Graph

```text
ReservationLifecycleEventOrchestrator (port)
└── DeterministicReservationLifecycleEventOrchestrator
    ├── ReservationLifecycleWorkflow (4.3A)
    └── ReservationLifecycleWorkflowStore (4.3B)
        └── PostgreSqlReservationLifecycleWorkflowRepository (composition 4.3C)
```

L'implémentation d'orchestration dépend uniquement des types Application. Elle ne connaît pas l'adaptateur PostgreSQL concret, PDO, Laravel, HTTP, l'Outbox, le Worker, un Consumer ou une Projection.

Laravel enregistre un singleton paresseux et un alias unique. Runtime Health reste inchangé à 25 capacités et demeure `Healthy`.
