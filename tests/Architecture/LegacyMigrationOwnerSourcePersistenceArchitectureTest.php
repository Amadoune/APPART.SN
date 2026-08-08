<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LegacyMigrationOwnerSourcePersistenceArchitectureTest extends TestCase
{
    public function test_application_owner_source_has_five_independent_streams(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/LegacyMigration/Application/OwnerSource';
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), glob($root.'/*.php') ?: []));
        foreach (['Inventory', 'Wave', 'Reconciliation', 'Quarantine', 'Cutover'] as $stream) {
            self::assertStringContainsString('append'.$stream, $php);
            self::assertStringContainsString('read'.$stream, $php);
        }
        foreach (['Infrastructure\\', 'PDO', 'PostgreSql', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox', 'Provider'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_repository_contains_transactional_guarantees_and_only_migration_084_is_added(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/PostgreSqlLegacyMigrationOwnerSource.php');
        foreach (['SAVEPOINT', 'pg_advisory_xact_lock', 'FOR UPDATE', 'ON CONFLICT', 'effective_at<=', 'recorded_at<='] as $guarantee) {
            self::assertStringContainsString($guarantee, $repository);
        }
        $migrations = glob($root.'/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/*.sql') ?: [];
        self::assertCount(2, $migrations);
        self::assertStringContainsString('084_legacy_migration_owner_source.sql', implode('|', $migrations));
        self::assertStringContainsString('084_legacy_migration_owner_source.down.sql', implode('|', $migrations));
    }
}
