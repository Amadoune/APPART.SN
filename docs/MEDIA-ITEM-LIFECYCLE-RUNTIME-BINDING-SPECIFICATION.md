# Media Item Lifecycle Runtime Binding Specification

```text
MediaItemLifecycleWorkflow
MediaItemLifecycleWorkflowMapper
PostgreSqlMediaItemLifecycleWorkflowRepository
    ├── PDO PostgreSQL Runtime existant
    └── MediaItemLifecycleWorkflowMapper
MediaItemLifecycleWorkflowStore
    └── alias exact du repository
```

Tous les composants sont des singletons. L'interface et l'implémentation du store partagent exactement la même instance.
