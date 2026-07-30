# Phase 4.7D — Orchestration Sequence

## Nominal

1. lecture via `AdministrativeActionContextualTransitionStore` ;
2. contrôle explicite de `expectedVersion` ;
3. décision via `AdministrativeActionLifecycleWorkflow` avec le Decision Context reçu ;
4. absence d'écriture après `Denied` ;
5. append exact via le store contextuel ;
6. restitution fermée.

## Rejeu

1. détection de `currentVersion = expectedVersion + 1` ;
2. inspection via `AdministrativeActionContextualReplayInspector` ;
3. classification par `AdministrativeActionReplayPolicy` ;
4. restitution sans appel au Workflow ni append.
