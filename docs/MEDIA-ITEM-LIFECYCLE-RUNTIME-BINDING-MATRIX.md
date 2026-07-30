# Media Item Lifecycle Runtime Binding Matrix

| Contrat | Implémentation | Cycle |
|---|---|---|
| `MediaItemLifecycleWorkflow` | lui-même | singleton paresseux |
| `MediaItemLifecycleWorkflowMapper` | lui-même | singleton paresseux |
| `PostgreSqlMediaItemLifecycleWorkflowRepository` | lui-même | singleton paresseux |
| `MediaItemLifecycleWorkflowStore` | alias repository | même singleton |
| `PDO` | connexion Runtime existante | réutilisée |
