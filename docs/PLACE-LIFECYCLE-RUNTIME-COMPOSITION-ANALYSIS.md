# Place Lifecycle Runtime Composition Analysis

## Graphe

```text
PlaceLifecycleWorkflow

PlaceLifecycleWorkflowStore
    → PostgreSqlPlaceLifecycleWorkflowStore
        → PDO
        → PlaceLifecycleWorkflowMapper
```

Le graphe est uniquement déclaratif au bootstrap. Aucune méthode du store,
aucune requête et aucune transaction n'est exécutée pendant l'enregistrement.

## Frontière

La composition ne crée aucune Orchestration et n'ajoute aucune décision. Elle
ne modifie ni `PlaceMergeContextV1`, ni le Workflow, ni le journal 038.

Sont absents : Event, Transport, Routing, Outbox, Consumer, Worker et HTTP.

## Santé

Runtime Health résout les composants uniquement lors de l'inspection explicite.
La santé vérifie la présence et la résolution du Workflow et de son port de
store. Elle ne lit aucune donnée et n'ouvre aucune transaction.
