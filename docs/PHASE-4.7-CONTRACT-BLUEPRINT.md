# Phase 4.7 — Administrative Action Lifecycle Contract Blueprint

## États candidats

États fermés : `Draft`, `PendingApproval`, `Recorded`, `Approved`, `Rejected`.

`Recorded`, `Approved` et `Rejected` sont terminaux. La création produit explicitement `Draft` hors workflow.

## Actions candidates

Actions fermées : `Record`, `Approve`, `Reject`, `Unknown`.

`AddReason` reste hors workflow de statut : il prépare une preuve requise, mais ne change pas l'état.

## Transitions candidates

| État source | Action | Précondition propriétaire | État cible |
|---|---|---|---|
| Draft | Record | motif présent + `DirectRecording` | Recorded |
| Draft | Record | motif présent + `IndependentApprovalRequired` | PendingApproval |
| PendingApproval | Approve | décideur indépendant + décision explicite | Approved |
| PendingApproval | Reject | décideur indépendant + décision explicite | Rejected |

Tous les autres couples sont refusés. La priorité diagnostique candidate est : `UnknownAction`, `TerminalState`, `MissingReason`, `ApprovalNotRequired`, `IndependentActorRequired`, `IncompatibleState`.

## Gate préalable au Workflow

La fonction ne peut pas honnêtement dépendre du seul couple `(state, action)`. Avant 4.7A, le Sprint **4.7A-R1 — Administrative Action Decision Context Contract** doit figer :

- une preuve fermée `ReasonPresent` ;
- une décision fermée `DirectRecording` ou `IndependentApprovalRequired` ;
- l'identité explicite de l'auteur et de l'acteur demandé ;
- la règle d'indépendance pour `Approve` et `Reject` ;
- la distinction entre décision métier et indisponibilité technique.

Ces valeurs proviennent exclusivement de `AdministrationAudit`. Le futur workflow les consomme sans appeler `FourEyesPolicy`, sans lire l'Aggregate et sans reconstruire une preuve.

## Déterminisme

Après certification du contexte, la décision dépend exclusivement de `(state, action, decisionContext)`. Aucune horloge, identité, lecture externe ou valeur implicite n'est autorisée.

## Rejeu et contexte d'exécution

Avant l'orchestration, un contrat versionné séparé devra porter `expectedVersion`, acteur, `occurredAt`, identités de décision/approbation lorsque requises et le contexte décisionnel certifié. Le dernier append devra être inspectable exactement avant toute classification `AlreadyApplied`, divergence ou conflit.

## Confidentialité

Les futurs événements ne pourront contenir ni texte de motif, ni contenu d'audit, ni donnée de la ressource cible au-delà d'une identité technique strictement nécessaire. La politique exacte sera certifiée avant le contrat événementiel.
