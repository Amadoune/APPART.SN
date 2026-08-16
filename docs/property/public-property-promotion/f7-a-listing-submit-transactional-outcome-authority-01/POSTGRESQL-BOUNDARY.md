# Frontière PostgreSQL

Les bindings productifs injectent la même instance `PDO` dans :

- `PostgreSqlListingTransaction` ;
- `PostgreSqlListingRepository` ;
- `PostgreSqlAuthoringPublicFactHandoff` ;
- `PostgreSqlListingPublicationWorkflowRepository` ;
- `PostgreSqlAggregateOutboxTransaction` et writer Outbox ;
- `PostgreSqlPublicationReviewQueue`.

La transaction Listing externe est owner du `BEGIN/COMMIT`. Les composants internes détectent `PDO::inTransaction()` et participent soit directement, soit au moyen de savepoints. La libération d’un savepoint ne committe pas la transaction externe.

Un rollback de la transaction externe peut donc annuler l’ensemble de ces écritures. Aucune table ni colonne supplémentaire n’est requise ; migration 101 interdite et inutile.
