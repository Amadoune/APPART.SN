# Listing Publication Workflow Specification

## Contrat

```php
ListingPublicationWorkflow::decide(
    ListingPublicationState $state,
    ListingPublicationAction $action,
): ListingPublicationDecision
```

## Décisions

- `Allowed` : transition unique, aucun diagnostic.
- `Denied` : aucun changement d'état, diagnostic unique.

## Diagnostics

- `UnknownAction` : sentinelle d'action non reconnue à la frontière future.
- `TerminalState` : toute action depuis Archived.
- `IncompatibleState` : l'action viserait l'état déjà détenu.
- `TransitionForbidden` : action connue mais absente de la matrice pour cet état.
- `WorkflowCorrupted` : code contractuel réservé à une matérialisation future incohérente; la matrice constante 4.1A ne peut pas le produire.

Ordre de décision : action inconnue, état terminal, transition autorisée, état incompatible, transition interdite. Aucune branche `default`.
