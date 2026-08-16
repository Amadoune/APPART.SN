# F7-A — Listing Submit Transactional Outcome Implementation 01

## Correction

La correction est limitée à `DeterministicPropertyListingAuthoringOperations::submit()`.

Le résultat `ListingPublicationOrchestrationResult` est capturé dans la closure transactionnelle. Le commit n’est permis que pour :

- `Applied` avec transition non nulle ;
- `AlreadyApplied` avec transition non nulle.

Tout autre outcome déclenche l’exception interne existante `AuthoringOperationRollback`. Après rollback, le résultat fermé capturé est réduit par le mapping Authoring existant : `Denied` vers `LifecycleConflict`, conflit vers `ConcurrentModification`, échec de persistance vers `DependencyUnavailable`.

Un succès structurellement invalide sans transition est rollbacké et réduit en indisponibilité fermée. Une exception technique sans résultat capturé suit la réduction technique existante.

## Périmètre préservé

`SubmitListing`, Domain Listing, Workflow, stores, transactions, Providers, HTTP, SQL et F6 sont inchangés. Aucune migration 101.
