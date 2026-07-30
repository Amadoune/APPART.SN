# Lead Lifecycle Workflow Analysis

## Décision

Le workflow applicatif `LeadLifecycleWorkflow` devient la référence unique des décisions de cycle de vie Lead. Il formalise les quatre états déjà portés par le modèle certifié sans modifier l'Aggregate `Lead`.

La création est l'entrée explicite du cycle via `initialState(): Created`. Elle n'est pas modélisée comme une transition artificielle `Created → Created` : les quatre seules transitions représentent des changements d'état réels.

## Périmètre métier

- `Created` accepte `Deliver` ou `Reject`.
- `Delivered` et `Rejected` acceptent `Close`.
- `Closed` est terminal.
- `Unknown` est une valeur contractuelle de refus et jamais une action exécutable.

Le workflow n'utilise ni identité, ni horloge, ni Aggregate, ni source d'éligibilité. Les preuves `ListingCatalog` et `AdvertiserCatalog` restent réservées à 4.4C-S1.

## Invariants

Une décision `Allowed` contient exactement une transition et aucun diagnostic. Une décision `Denied` contient exactement un diagnostic et aucune transition. Les 16 couples état/action sont classés explicitement, sans branche `default`.
