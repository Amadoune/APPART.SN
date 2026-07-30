# Place Lifecycle Workflow Specification

## Entrées

`PlaceLifecycleWorkflow::decide` reçoit exclusivement :

- `PlaceLifecycleCurrentState` : identité de la source et état courant;
- `PlaceLifecycleAction` : `Enable`, `Disable` ou `Merge`;
- `PlaceMergeContextV1` certifié.

L'identité dans `CurrentState` permet de refuser un contexte rattaché à une
autre source par `InvalidContext`. Aucune lecture externe n'est nécessaire.

## États

- état initial : `Enabled`;
- état réversible : `Disabled`;
- état irréversiblement terminal : `Merged`.

## Pureté

Le Workflow ne dépend que de valeurs en mémoire. Il ne possède aucun port,
service, Repository, Aggregate, Runtime, framework, persistance, inspection,
rejeu, Event ou transport.

Les preuves de cible du contexte ne sont évaluées que pour l'action `Merge`.
Elles n'affectent pas `Enable` ou `Disable`.

## Résultats fermés

- `Applied`;
- `AlreadyInState`;
- `TerminalState`;
- `SameIdentity`;
- `TargetDisabled`;
- `TargetMerged`;
- `DifferentType`;
- `DifferentCountry`;
- `InvalidContext`.

Un résultat appliqué porte une transition exacte. Un refus conserve l'état
courant et ne porte aucune transition.
