# Lead Lifecycle Runtime Binding Matrix

| Contrat ou composant | Résolution de production | Cycle de vie | Identité attendue |
|---|---|---|---|
| `LeadLifecycleWorkflow` | classe elle-même | Singleton paresseux | stable |
| `LeadLifecycleWorkflowMapper` | classe elle-même | Singleton paresseux | stable |
| `PostgreSqlLeadLifecycleWorkflowRepository` | classe elle-même | Singleton paresseux | stable |
| `LeadLifecycleWorkflowStore` | alias du repository | Singleton partagé | identique au repository |
| `PDO` | connexion PostgreSQL Runtime existante | préexistant | réutilisée |

Il existe exactement une déclaration pour chaque singleton et exactement un alias du store.
