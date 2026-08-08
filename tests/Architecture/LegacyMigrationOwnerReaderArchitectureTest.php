<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class LegacyMigrationOwnerReaderArchitectureTest extends TestCase
{
    public function test_owner_readers_depend_only_on_owner_source_and_public_contracts(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/LegacyMigration/Application/OwnerReader';
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php .= (string) file_get_contents($file->getPathname());
            }
        }

        self::assertSame(5, substr_count($php, 'implements LegacyMigration') - 1);
        self::assertStringContainsString('LegacyMigrationOwnerSource', $php);
        self::assertStringNotContainsString('default', $php);
        foreach (['Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'SQL', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Routing', 'Transport'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_exposes_five_public_aliases_and_one_policy_alias(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/LegacyMigrationOwnerReaderServiceProvider.php');
        foreach (['LegacyMigrationInventoryReaderV1', 'LegacyMigrationWaveReaderV1', 'LegacyMigrationReconciliationReaderV1', 'LegacyMigrationQuarantineReaderV1', 'LegacyMigrationCutoverReaderV1'] as $contract) {
            self::assertSame(1, preg_match_all('/->alias\([^;]+,\s*'.$contract.'::class\);/', $provider));
        }
        self::assertSame(1, preg_match_all('/->alias\([^;]+,\s*LegacyMigrationOwnerReaderV1::class\);/', $provider));
        self::assertSame(6, substr_count($provider, '->singleton('));
        foreach (['PostgreSql', 'Mapper', 'PDO', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }

        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'LegacyMigrationOwnerReaderServiceProvider'));
    }

    public function test_migration_084_remains_unchanged(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('a0450f8d6553c4fc61e924ded877d45e118f987115dec49532ae7f6f878b7591', hash_file('sha256', $root.'084_legacy_migration_owner_source.sql'));
        self::assertSame('e7ca6a6c825fe1f209ef8283eb1daa6f9f653631903783014c0e6f362ca41d4d', hash_file('sha256', $root.'084_legacy_migration_owner_source.down.sql'));
    }
}
