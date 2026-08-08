<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SearchOwnerSourceRuntimeReadArchitectureTest extends TestCase
{
    public function test_application_runtime_read_is_framework_and_infrastructure_independent(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchOwnerSourceRuntimeRead';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'Mapper', 'RuntimeHealth', 'OwnerReader', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_provider_has_one_runtime_read_alias_and_no_persistence_access(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/SearchOwnerSourceRuntimeReadServiceProvider.php');
        self::assertSame(1, preg_match_all('/->alias\(DeterministicSearchOwnerSourceRuntimeReadV1::class, SearchOwnerSourceRuntimeReadV1::class\)/', $contents));
        self::assertStringContainsString('singleton', $contents);
        foreach (['PostgreSql', 'Mapper', 'PDO', 'DatabaseManager', 'RuntimeHealth', 'OwnerReader'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_migration_075_remains_frozen(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/075_search_owner_source.sql');
        self::assertSame('0b4f54833eb452fe944ec82f5468ea329b42b4d5d0b4aebaef38200ed00a65dc', hash('sha256', $migration));
    }
}
