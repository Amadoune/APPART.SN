# Lead Lifecycle Runtime Health Extension Matrix

| Capacité | Contrat inspecté | Inspection autorisée | Appel interdit |
|---|---|---|---|
| `LeadLifecycleWorkflow` | `LeadLifecycleWorkflow` | binding, type, constructibilité | `decide()`, `initialState()` |
| `LeadLifecycleWorkflowStore` | `LeadLifecycleWorkflowStore` | binding, type, constructibilité | `read()`, `initialize()`, `append()` |

Runtime Health passe explicitement de 27 à 29 capacités. Les deux nouvelles capacités sont structurelles. L'inspection ne prend aucune décision, ne lit aucune ligne et n'ouvre aucune transaction.
