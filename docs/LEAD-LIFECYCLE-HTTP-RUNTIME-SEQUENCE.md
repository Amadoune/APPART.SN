# Lead Lifecycle HTTP Runtime Sequence

```text
HTTP POST
→ validation stricte du transport
→ LeadLifecycleAtomicEventRequest
→ LeadLifecycleAtomicEventOrchestrator
→ LeadLifecycleOrchestrationResult
→ mapping HTTP fermé
→ réponse JSON
```

La couche HTTP n'ouvre aucune transaction. L'atomicité, le rollback et l'idempotence restent exclusivement ceux de 4.4I.
