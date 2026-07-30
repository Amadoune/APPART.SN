# Media Item Lifecycle Orchestration Sequence Specification

## Chemin nominal

```text
read
→ contrôle expectedVersion
→ MediaItemLifecycleWorkflow.decide()
→ MediaItemLifecycleContextualTransitionStore.append()
```

Une décision `Denied` termine le traitement sans append.

## Chemin de rejeu

```text
read
→ détection currentVersion = expectedVersion + 1
→ MediaItemLifecycleContextualReplayInspector.inspectLatest()
→ MediaItemLifecycleReplayPolicy.classify()
```

Le workflow n'est jamais rappelé sur ce chemin. Aucun objet `MediaItemLifecycleTransition` n'y est construit.

## Concurrence

Le store contextuel reste propriétaire du verrou et de l'atomicité. L'orchestrateur propage ses résultats fermés sans compensation ni seconde écriture.
