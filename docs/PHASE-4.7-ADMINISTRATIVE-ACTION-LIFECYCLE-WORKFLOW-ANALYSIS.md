# Phase 4.7A — Administrative Action Lifecycle Workflow Analysis

Le Workflow possède exclusivement les décisions de statut. Il consomme le `AdministrativeActionDecisionContext` V1 certifié et n'accède ni à l'Aggregate historique, ni à `FourEyesPolicy`, ni au texte du motif.

La création reste hors workflow et produit `Draft` par le mécanisme historique. `AddReason` reste hors workflow puisqu'il ne change aucun statut.

Le Workflow est une fonction pure :

```text
(state, action, decisionContext V1) → Allowed | Denied
```

Les indisponibilités techniques ne sont pas acceptées par `decide()`. Seul un contexte `Available` résolu en amont peut fournir le contexte métier.
