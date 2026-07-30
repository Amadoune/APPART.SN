# Place Lifecycle Runtime Orchestration — Spécification

## Périmètre

`PlaceLifecycleOrchestrator` coordonne exclusivement les contrats certifiés :

```text
Replay Inspection
    ↓
Replay Classifier si Found
    ↓
PlaceLifecycleWorkflow
    ↓
PlaceLifecycleWorkflowStore
```

Son entrée est composée de :

- `PlaceLifecycleCurrentState`;
- `PlaceLifecycleAction`;
- `PlaceMergeContextV1` déjà qualifié.

L'orchestrateur ne qualifie pas la cible, ne relit pas de projection, ne
reconstruit pas une preuve et ne prend aucune décision métier.

## Précédence

1. `Inspection::Corrupted` ferme le traitement par `InspectionCorrupted`.
2. `Inspection::Missing` poursuit directement vers le Workflow.
3. `Inspection::Found` appelle le classificateur.
4. Une divergence ou un conflit du classificateur ferme le traitement.
5. `Classifier::AlreadyApplied` est uniquement un candidat de rejeu.
6. Le Workflow produit un refus fermé ou une transition candidate.
7. Seule la Persistance confirme `AlreadyApplied`.

## Confirmation du rejeu

Lorsque le classificateur produit `AlreadyApplied`, la transition candidate est
présentée au store avec le contexte V1 inchangé :

```text
Persistence::AlreadyApplied
→ AlreadyApplied final

Persistence::SourceVersionConflict
→ ReplayConflict
```

Cette séquence garantit qu'une action différente, incluse dans l'empreinte
persistante, ne peut jamais être confirmée comme déjà appliquée.

## Frontières

Le composant ne dépend d'aucun Runtime Laravel, PostgreSQL concret, Event,
Transport, Routing, Outbox, HTTP, Consumer ou Worker. Aucun binding Runtime
n'est ajouté pendant 4.8E.
