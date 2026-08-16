# Preuves d’atomicité transactionnelle

## Promotion

La transaction locale F6 couvre le write Property et le ledger. Le test PostgreSQL injectant un ledger indisponible démontre le rollback de la Property et l’absence de ligne de succès. Les advisory locks command/property/authoring et le participant transactionnel Property assurent l’unicité locale.

## Première divergence F7

L’atomicité de la transaction Listing postérieure n’est pas garantie pour un résultat d’orchestration fermé non réussi. `PostgreSqlListingTransaction` rollback uniquement sur exception ; `DeterministicPropertyListingAuthoringOperations::submit()` retourne le résultat non réussi hors de la transaction sans transformer ce résultat en rollback.

Conséquence possible : Promotion/Property durable, Aggregate Listing avancé, Workflow Listing non avancé. Le retry ne possède plus nécessairement la paire d’états attendue.

Cette divergence est la cause unique du **NO GO PROPOSÉ** F7.
