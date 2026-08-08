<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SearchQueryResolutionOwnerSourceRuntimeArchitectureTest extends TestCase
{
    public function test_application_runtime_depends_only_on_the_owner_port(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchQueryResolutionOwnerSourceRuntime';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'Mapper', 'Reader', 'RuntimeHealth', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\', 'SearchDocumentId', 'SearchIndexId'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_provider_bindings_are_unique_singletons(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/SearchQueryResolutionOwnerSourceRuntimeServiceProvider.php');
        self::assertSame(1, preg_match_all('/->alias\(PostgreSqlSearchQueryResolutionOwnerSource::class, SearchQueryResolutionOwnerSource::class\)/', $contents));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicSearchQueryResolutionOwnerSourceRuntimeV1::class, SearchQueryResolutionOwnerSourceRuntimeV1::class\)/', $contents));
        self::assertStringContainsString('singleton', $contents);
    }

    public function test_persistence_migration_is_frozen(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/076_search_query_resolution_owner_source.sql');
        self::assertSame('4439ef5640e9349b50aeb8e7166c66ac4fb05d6ae41b94e90c9cec0467a963b2', hash('sha256', $migration));
    }
}
