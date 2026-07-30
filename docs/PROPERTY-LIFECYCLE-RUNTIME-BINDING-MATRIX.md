# Property Lifecycle Runtime Binding Matrix

| Contrat ou composant | Implémentation | Cycle de vie | Dépendances |
|---|---|---|---|
| `PropertyLifecycleWorkflow` | lui-même | singleton paresseux | aucune |
| `PropertyLifecycleWorkflowMapper` | lui-même | singleton paresseux | aucune |
| `PostgreSqlPropertyLifecycleWorkflowRepository` | lui-même | singleton paresseux | PDO Runtime, mapper |
| `PropertyLifecycleWorkflowStore` | alias du repository | même singleton | aucune duplication |

Il n'existe qu'un binding de production par ligne et aucun binding conditionnel.
