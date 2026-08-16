# Preuves rollback et retry

Référence : campagne F7-A Implementation.

- Unit : 7 tests, 56 assertions, PASS.
- PostgreSQL : 5 tests, 51 assertions, PASS.
- Rejeu terminal Unit/PostgreSQL/Architecture : 15 tests, 149 assertions, PASS.

Le rollback englobe Aggregate, Workflow, public facts, outbox, delivery et PublicationReview. La transaction Promotion distincte demeure committée. Le retry réutilise l’unique ledger Promotion et converge sans duplication.
