# Delta audit

## Test-only compatibility correction

`PostgreSqlMediaIngestionMigrationRollbackTest.php` contains exactly two
qualified substitutions:

```sql
c.relkind::text
```

Its materialized blob is `f23b324e44c8eb824219fc023bb4ca82e6506557`,
identical to the dynamically qualified overlay.

## Savepoint contract correction

`PostgreSqlPublicProjectionOutboxIntegrationTest.php` is the exact qualified
test-only correction. Its blob is
`ad92ade904c21487391170f66fe7ef74c2643640`, identical to both the qualified
overlay and historical authorized correction.

The obsolete rejection assertion is replaced by evidence for:

- Aggregate + Outbox atomicity;
- successful participation in an outer transaction through a savepoint;
- full outer rollback without partial effects;
- rollback to savepoint after an inner failure;
- preservation of writes made before the savepoint;
- propagation of the original exception;
- continued ownership of commit/rollback by the outer transaction.

## Production boundary

No production implementation is changed beyond the already-authorized F22 SQL
delta inherited from `e0f76f6b`. No other SQL file changes. Migration 058 has
blob `e355cb5a4ba1176b057574b31865485628fddbc5`, exactly the qualified F22 blob.
