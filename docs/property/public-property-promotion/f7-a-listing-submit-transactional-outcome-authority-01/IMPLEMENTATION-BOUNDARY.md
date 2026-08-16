# Frontière de la future implémentation

## Fichier produit autorisé

- `app/Application/PropertyListingAuthoringOperations/DeterministicPropertyListingAuthoringOperations.php` : déclencher le rollback interne sur tout outcome Workflow non committable, conserver puis réduire le résultat fermé.

L’exception locale `AuthoringOperationRollback`, déjà déclarée dans ce fichier, suffit comme signal. Sa définition ne requiert pas nécessairement de changement.

## Preuves autorisées

- test Unit `AuthoringSubmissionHandoffTest` pour outcome fermé et conservation du mapping ;
- test PostgreSQL `PostgreSqlAuthoringOperationsTest` pour rollback réel, retry et absence de handoffs partiels ;
- tests Architecture ciblés si nécessaires.

## Hors périmètre

Transactions, stores, Workflow, Domain Listing, F6, Providers, HTTP, SQL, migrations, Projection et Search. Aucun refactoring général.
