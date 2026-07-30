# Professional Status Orchestration Sequence Specification

```text
read(professionalId)
├─ Missing → Missing
├─ Corrupted → PersistenceCorrupted
├─ currentVersion = expectedVersion
│  └─ workflow.decide(state, action)
│     ├─ Denied → Denied
│     └─ Allowed → contextualStore.append(exact transition)
├─ currentVersion = expectedVersion + 1
│  └─ inspector.inspectLatest()
│     └─ replayPolicy.classify(action, context, snapshot)
└─ autre version → VersionConflict
```

Une décision refusée n'appelle jamais `append()`. Le workflow n'est jamais rappelé sur le chemin de rejeu.
