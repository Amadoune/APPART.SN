# Listing Publication Runtime Orchestration Specification

## Entrée

`ListingPublicationOrchestrationRequest` contient un `ListingId`, une `ListingPublicationAction` et une version attendue strictement positive.

## Séquence normative

1. `ListingPublicationWorkflowStore::read` charge l'état courant.
2. La version lue est comparée à la version attendue.
3. `ListingPublicationWorkflow::decide` reçoit exactement l'état lu et l'action demandée.
4. Une décision `Denied` est retournée avec son diagnostic exact, sans écriture.
5. La transition d'une décision `Allowed` est transmise à `append` avec la version suivante.
6. Le résultat fermé du store est traduit mécaniquement en résultat d'orchestration.

## Graphe Runtime

`ListingPublicationOrchestrator` est l'alias unique du singleton `DeterministicListingPublicationOrchestrator`. Celui-ci reçoit les instances certifiées de `ListingPublicationWorkflow` et `ListingPublicationWorkflowStore`. Le binding reste paresseux et n'exécute aucune transition au bootstrap.

## Exclusions

Aucun événement, Outbox, HTTP, Projection, Aggregate, fallback ou implémentation de production alternative n'est introduit.
