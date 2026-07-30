# Reservation Lifecycle Atomic Integration Sequence

```text
ReservationLifecycleAtomicEventRequest
  → PostgreSqlAggregateOutboxTransaction::run
    → ReservationLifecycleEventOrchestrator::transition
      → Workflow decide
      → ReservationLifecycleWorkflowStore append
    → ReservationLifecycleEventCatalog::eventFor
    → ReservationLifecycleDeliveryPayload
    → PublicProjectionDeliveryCatalogMessageFactory
    → PublicProjectionOutboxWriter::append
  → commit
```

Toute exception ou issue `PersistenceCorrupted` quitte la séquence par rollback. Aucun routeur, Consumer, Worker ou adaptateur HTTP ne participe à la transaction de production.
