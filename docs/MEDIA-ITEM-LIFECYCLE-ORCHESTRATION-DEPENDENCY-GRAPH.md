# Media Item Lifecycle Orchestration Dependency Graph

```text
MediaItemLifecycleOrchestrator
└── DeterministicMediaItemLifecycleOrchestrator
    ├── MediaItemLifecycleWorkflow
    ├── MediaItemLifecycleContextualTransitionStore
    │   └── PostgreSqlMediaItemLifecycleContextualTransitionRepository
    ├── MediaItemLifecycleContextualReplayInspector
    │   └── PostgreSqlMediaItemLifecycleContextualReplayInspector
    └── MediaItemLifecycleReplayPolicy
```

Le graphe est composé par le Provider Laravel existant avec des singletons et alias uniques. Il ne contient aucune dépendance vers `MediaCollection`, HTTP, Event, Inbox, Outbox, Consumer ou Worker.

Runtime Health expose uniquement `MediaItemLifecycleOrchestrator` comme nouvelle capacité publique. Les composants de persistance contextuelle restent des détails internes.
