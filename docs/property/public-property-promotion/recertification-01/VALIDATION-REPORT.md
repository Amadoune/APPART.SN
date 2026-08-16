# Rapport de validation F7

Date : 2026-08-14.

## Preuves héritées F6 vérifiées

- Promotion + Submit ciblés : 23 tests, 122 assertions, PASS lors de la certification F6 ;
- régressions F2–F5-A : 56 tests, 338 assertions, PASS ;
- PHPStan ciblé : PASS, 0 erreur ;
- Pint ciblé : PASS ;
- PostgreSQL réel : Applied, replay, divergence, owner/version/incomplet, Geography négative, rollback ledger, migration 100 et Submit positif/négatif.

## Audit F7

L’audit statique de `DeterministicPropertyListingAuthoringOperations::submit()`, `SubmitListing`, `DeterministicListingPublicationOrchestrator` et `PostgreSqlListingTransaction` démontre la première divergence transactionnelle après Promotion.

La campagne F7 est arrêtée fail-fast avant extension des preuves Geography et avant toute mutation produit. Seul `git diff --check` est exécuté après production documentaire.

## Qualification

Validation terminale F7 : **NON ATTEINTE**. Cause unique : rollback Listing non garanti lorsque le workflow retourne un résultat fermé non réussi après l’écriture Aggregate.
