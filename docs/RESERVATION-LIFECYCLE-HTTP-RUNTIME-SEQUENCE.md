# Reservation Lifecycle HTTP Runtime Sequence

```text
HTTP POST
  → route UUID
  → ReservationLifecycleTransitionRequest
  → ReservationLifecycleAtomicEventRequest
  → ReservationLifecycleHttpController
  → ReservationLifecycleAtomicEventOrchestrator (un appel)
  → ReservationLifecycleHttpResultMapper
  → JSON + statut HTTP fermé
```

L'adaptateur ne démarre aucune transaction. La transaction demeure exclusivement propriétaire de l'intégrateur atomique 4.3I.
