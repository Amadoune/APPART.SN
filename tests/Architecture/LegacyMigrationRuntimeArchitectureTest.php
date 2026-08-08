<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class LegacyMigrationRuntimeArchitectureTest extends TestCase
{
    public function test_application_runtime_depends_only_on_the_owner_port(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/LegacyMigration/Application/Runtime';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'Mapper', 'RuntimeRead', 'OwnerReader', 'ReaderV1', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Transport', 'Routing', 'SQL'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_provider_bindings_and_registration_are_unique(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/LegacyMigrationRuntimeServiceProvider.php');
        self::assertSame(1, preg_match_all('/->alias\(PostgreSqlLegacyMigrationOwnerSource::class, LegacyMigrationOwnerSource::class\)/', $provider));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicLegacyMigrationRuntimeAvailabilityPolicy::class, LegacyMigrationRuntimeAvailabilityPolicy::class\)/', $provider));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicLegacyMigrationRuntime::class, LegacyMigrationRuntimeV1::class\)/', $provider));
        self::assertStringContainsString('singleton', $provider);

        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertSame(2, substr_count($providers, 'LegacyMigrationRuntimeServiceProvider'));
    }

    public function test_migration_084_is_frozen_by_sha256(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/LegacyMigration/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('a0450f8d6553c4fc61e924ded877d45e118f987115dec49532ae7f6f878b7591', hash_file('sha256', $root.'084_legacy_migration_owner_source.sql'));
        self::assertSame('e7ca6a6c825fe1f209ef8283eb1daa6f9f653631903783014c0e6f362ca41d4d', hash_file('sha256', $root.'084_legacy_migration_owner_source.down.sql'));
    }
}
