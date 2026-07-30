# Property Lifecycle Runtime Orchestration Specification

## Entrée

`PropertyLifecycleOrchestrationRequest` contient un `PropertyId`, une `PropertyLifecycleAction` et une version attendue strictement positive.

## Algorithme normatif

1. appeler `store.read(propertyId)` une fois ;
2. retourner un échec typé si la lecture est inutilisable ;
3. comparer la version courante à la version attendue ;
4. appeler `workflow.decide(state, action)` une fois ;
5. retourner immédiatement tout `Denied` avec son diagnostic exact ;
6. appeler `store.append(propertyId, transition, expectedVersion + 1)` une fois pour `Allowed` ;
7. traduire mécaniquement le résultat fermé du store.

Le port `PropertyLifecycleOrchestrator` est lié à une unique implémentation de production, résolue paresseusement par Laravel.
