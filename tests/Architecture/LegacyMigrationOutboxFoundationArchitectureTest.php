<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LegacyMigrationOutboxFoundationArchitectureTest extends TestCase
{
    public function test_application_outbox_has_only_five_deliveries_as_sources(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/LegacyMigration/Application/Outbox';
        $files = glob($root.'/*.php') ?: [];
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        self::assertCount(5, $files);
        foreach (['Inventory', 'Wave', 'Reconciliation', 'Quarantine', 'Cutover'] as $stream) {
            self::assertStringContainsString('LegacyMigration'.$stream.'DeliveryV1', $php);
        }
        foreach (['Application\\Event', 'PublicRead', 'OwnerReader', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'PDO', 'SQL', 'Infrastructure\\', 'Provider', 'Transport', 'Routing', 'Consumer'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_repository_guarantees_and_migrations_are_scoped(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/LegacyMigration/Infrastructure/Outbox/PostgreSqlLegacyMigrationOutboxRepository.php');
        foreach (['SAVEPOINT', 'FOR UPDATE', 'ON CONFLICT', 'ORDER BY created_at,message_id', 'MAX_RETRIES'] as $guarantee) {
            self::assertStringContainsString($guarantee, $repository);
        }
        $migrations = $root.'/src/Modules/LegacyMigration/Infrastructure/Outbox/Migrations/';
        self::assertFileExists($migrations.'085_legacy_migration_outbox.sql');
        self::assertFileExists($migrations.'085_legacy_migration_outbox.down.sql');
        $ownerMigrations = $root.'/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('a0450f8d6553c4fc61e924ded877d45e118f987115dec49532ae7f6f878b7591', hash_file('sha256', $ownerMigrations.'084_legacy_migration_owner_source.sql'));
        self::assertSame('e7ca6a6c825fe1f209ef8283eb1daa6f9f653631903783014c0e6f362ca41d4d', hash_file('sha256', $ownerMigrations.'084_legacy_migration_owner_source.down.sql'));
    }
}
