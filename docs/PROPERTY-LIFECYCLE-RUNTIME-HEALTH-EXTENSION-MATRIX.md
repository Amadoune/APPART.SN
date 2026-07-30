# Property Lifecycle Runtime Health Extension Matrix

| Capacité | Contrat inspecté | Vérification | Méthodes interdites pendant l'inspection |
|---|---|---|---|
| Property Lifecycle Workflow | `PropertyLifecycleWorkflow` | déclaré, résolu, compatible | `decide` |
| Property Lifecycle Workflow Store | `PropertyLifecycleWorkflowStore` | déclaré, résolu, compatible | `read`, `initialize`, `append` |

Les deux capacités sont obligatoires. Leur absence ou incompatibilité suit les diagnostics Runtime Health certifiés. Avec le graphe de production complet, le résultat demeure `Healthy`.
