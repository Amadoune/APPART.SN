# Professional Status Transition Context Foundation Analysis

## Décision contractuelle

Toute future commande de transition porte explicitement un acteur, un instant métier UTC et une version attendue strictement positive. Aucun de ces éléments ne peut être produit par l'infrastructure ou déduit de l'état courant.

`ProfessionalStatusActorId` et `ProfessionalStatusOccurredAt` sont des Value Objects Domain immuables. `ProfessionalStatusExpectedVersion` et `ProfessionalStatusTransitionContext` forment le contrat applicatif de commande.

## Rejeu

Le rejeu ne peut pas rappeler le workflow ni reconstruire une transition. Lorsque la version courante correspond à `expectedVersion + 1`, le futur orchestrateur devra inspecter le dernier append exact par `ProfessionalStatusContextualReplayInspector`.

- transition, version et contexte identiques : `AlreadyApplied` ;
- même transition et même version, acteur ou instant différent : `ContextDivergence` ;
- autre divergence : `Conflict` ;
- inspection absente ou corrompue : résultat technique fermé correspondant.

## Périmètre

Cette fondation ne fournit aucune implémentation, persistance, migration, composition Runtime ou orchestration. Le workflow, le store historique, le journal 027 et Runtime Health restent inchangés.
