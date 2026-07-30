# Property Lifecycle Orchestrator Runtime Binding Matrix

| Contrat | Implémentation | Cycle de vie | Dépendances exactes |
|---|---|---|---|
| `PropertyLifecycleOrchestrator` | `DeterministicPropertyLifecycleOrchestrator` | singleton paresseux, alias unique | `PropertyLifecycleWorkflow`, `PropertyLifecycleWorkflowStore` |

Runtime Health inspecte le port comme vingtième capacité obligatoire. L'inspection construit seulement le graphe ; elle n'appelle jamais `transition`, le workflow ou le store.
