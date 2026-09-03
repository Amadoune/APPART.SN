# Materialization manifest

The exact R5-to-materialized-source file set contains eleven paths.

## Existing F22 delta

1. `docs/release/rc2-r5-f22-migration-058-rollback-corrective-amendment-01/CORRECTIVE-AMENDMENT.md`
2. `src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/058_media_ingestion.down.sql`
3. `tests/Architecture/MediaIngestionPersistenceArchitectureTest.php`
4. `tests/PostgreSQL/MediaIngestionMigration/PostgreSqlMediaIngestionMigrationRollbackTest.php`

## Baseline test correction

5. `tests/PostgreSQL/PublicProjectionOutbox/PostgreSqlPublicProjectionOutboxIntegrationTest.php`

## Controlled-materialization evidence

6. `docs/release/rc2-full-replay-05-r5-f22-postgresql-baseline-test-corrections-controlled-materialization-01/SOURCE-IDENTITY.md`
7. `docs/release/rc2-full-replay-05-r5-f22-postgresql-baseline-test-corrections-controlled-materialization-01/MATERIALIZATION-MANIFEST.md`
8. `docs/release/rc2-full-replay-05-r5-f22-postgresql-baseline-test-corrections-controlled-materialization-01/DELTA-AUDIT.md`
9. `docs/release/rc2-full-replay-05-r5-f22-postgresql-baseline-test-corrections-controlled-materialization-01/POST-EXECUTION-INTEGRITY.md`
10. `docs/release/rc2-full-replay-05-r5-f22-postgresql-baseline-test-corrections-controlled-materialization-01/VALIDATION-REPORT.md`
11. `docs/release/rc2-full-replay-05-r5-f22-postgresql-baseline-test-corrections-controlled-materialization-01/NEXT-GATE.md`

No other path is authorized.
