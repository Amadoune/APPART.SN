# Phase 5.1E — Runtime Composition

## Owner

`IdentityAccessRuntimeServiceProvider` est l'unique Provider propriétaire de la
composition Identity & Access Completion.

Il compose exclusivement :

- `AccountRegistry`, déjà certifié ;
- `AccountStatusWorkflowStore`, déjà certifié ;
- `AccountClosureStateReader`, lecteur 5.1E ;
- `AccountAvailabilityInspector`, politique pure ;
- `IdentityAccessRuntimeHealthInspector`, preuve locale du graphe.

## Graphe

```text
AccountAvailabilityInspector
├── AccountRegistry
├── AccountStatusWorkflowStore
└── AccountClosureStateReader
    └── PDO PostgreSQL partagé
```

Les bindings sont lazy et singleton. Le Provider ne déclenche aucune requête,
transaction, écriture ou orchestration lors de sa résolution.

## Frontières

Le Provider ne contient aucun Controller, Route, Middleware, Event, Delivery,
Outbox ou orchestrateur métier. Il réutilise les ports certifiés sans modifier
leurs owners ni le Provider historique.

Le catalogue Runtime Health gelé reste strictement à 58 capacités. La santé
IAM est un rapport propriétaire séparé et ne réinterprète pas ce catalogue.
