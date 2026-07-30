# Media Item Lifecycle Runtime Health Extension Matrix

| Capacité | Vérification |
|---|---|
| `MediaItemLifecycleWorkflow` | binding présent, type compatible, constructible |
| `MediaItemLifecycleWorkflowStore` | binding présent, type compatible, constructible |

Runtime Health passe explicitement de 40 à 42 capacités. Le mapper et le repository ne sont pas exposés séparément : ils sont des détails de composition. L'inspection n'appelle aucune méthode métier ou de persistance.
