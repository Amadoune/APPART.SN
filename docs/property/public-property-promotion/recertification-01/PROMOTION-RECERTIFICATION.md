# F7 — Public Property Promotion Recertification 01

## Périmètre audité

La recertification porte sur l’implémentation F6, la migration 100, les contrats Promotion, le Provider, les bindings et les preuves Unit/Feature/Architecture/PostgreSQL existantes. Les verdicts historiques restent inchangés.

La chaîne F6 est matérialisée jusqu’à la précondition Submit : snapshot F4, owner/version/complétude, F2, F3, F5-A, `RegisterProperty`, registre Property et ledger durable.

## Arrêt fail-fast

La première divergence réelle apparaît dans le scénario « échec Listing après promotion » :

1. la Promotion est validée dans sa transaction locale ;
2. `SubmitListing::execute()` écrit l’Aggregate Listing dans la transaction Listing ;
3. l’orchestration du workflow peut retourner un résultat non réussi sans lever d’exception ;
4. la closure se termine normalement ;
5. `PostgreSqlListingTransaction::run()` committe ;
6. le résultat HTTP/Application reste pourtant un échec.

La transaction Listing ne rollback donc pas nécessairement l’écriture Aggregate. La paire Aggregate/Workflow peut diverger et le retry exigé par F7.18 n’est pas démontrable.

Conformément à la mission, aucune correction F6 ou Listing n’est réalisée et l’audit s’arrête à cette première divergence.
