# Phase 5.1E — Runtime Bindings

| Contrat | Implémentation | Cycle de vie |
|---|---|---|
| AccountClosureStateReader | PostgreSqlAccountClosureStateReader | singleton |
| AccountAvailabilityInspector | DeterministicAccountAvailabilityInspector | singleton |
| IdentityAccessRuntimeHealthInspector | DeterministicIdentityAccessRuntimeHealthInspector | singleton |

Les dépendances `AccountRegistry`, `AccountStatusWorkflowStore` et `PDO` sont
consommées par leurs bindings certifiés existants. 5.1E ne les redéfinit pas.

Le Provider est enregistré après `PublicProjectionRuntimeServiceProvider` afin
que ses dépendances certifiées soient déjà déclarées. La résolution reste lazy.

## Interdictions vérifiées

- aucun binding HTTP ;
- aucun Controller, Route ou Middleware ;
- aucun Event Transport, router ou consumer ;
- aucun Delivery ou Outbox ;
- aucun orchestrateur 5.1F ;
- aucune mutation de Runtime Health 58.
