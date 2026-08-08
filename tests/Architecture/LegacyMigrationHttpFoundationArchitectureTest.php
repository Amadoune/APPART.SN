<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LegacyMigrationHttpFoundationArchitectureTest extends TestCase
{
    public function test_http_surface_depends_only_on_five_public_readers(): void
    {
        $root = dirname(__DIR__, 2);
        $files = array_merge(glob($root.'/app/Http/Controllers/LegacyMigration*Controller.php'), glob($root.'/app/Http/Requests/LegacyMigration*Request.php'), glob($root.'/app/Http/LegacyMigration/*.php'));
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        foreach (['LegacyMigrationInventoryReaderV1', 'LegacyMigrationWaveReaderV1', 'LegacyMigrationReconciliationReaderV1', 'LegacyMigrationQuarantineReaderV1', 'LegacyMigrationCutoverReaderV1'] as $reader) {
            self::assertStringContainsString($reader, $php);
        }
        foreach (['LegacyMigrationOwnerSource', 'Application\\Runtime', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Transport', 'SQL'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_and_routes_are_unique(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/LegacyMigrationHttpServiceProvider.php');
        foreach (['LegacyMigrationResponseFactory', 'LegacyMigrationInventoryController', 'LegacyMigrationWaveController', 'LegacyMigrationReconciliationController', 'LegacyMigrationQuarantineController', 'LegacyMigrationCutoverController'] as $binding) {
            self::assertSame(1, substr_count($provider, 'singleton('.$binding.'::class'));
        }
        foreach (['inventory', 'wave', 'reconciliation', 'quarantine', 'cutover'] as $route) {
            self::assertSame(1, substr_count($provider, "name('legacy-migration.".$route."')"));
        }
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'LegacyMigrationHttpServiceProvider'));
    }

    public function test_migration_084_remains_unchanged(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('a0450f8d6553c4fc61e924ded877d45e118f987115dec49532ae7f6f878b7591', hash_file('sha256', $root.'084_legacy_migration_owner_source.sql'));
        self::assertSame('e7ca6a6c825fe1f209ef8283eb1daa6f9f653631903783014c0e6f362ca41d4d', hash_file('sha256', $root.'084_legacy_migration_owner_source.down.sql'));
    }
}
