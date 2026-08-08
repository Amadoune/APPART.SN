<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SearchQueryResolutionOwnerSourceRuntimeReadArchitectureTest extends TestCase
{
    public function test_application_runtime_read_depends_only_on_runtime_contract(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchQueryResolutionOwnerSourceRuntimeRead';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'Mapper', 'SearchQueryResolutionOwnerSource\\Contract', 'RuntimeHealth', 'OwnerReader', 'SearchExperiencePublicRead\\SearchQuery', 'SearchDocumentId', 'SearchIndexId', 'ListingId', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_provider_has_one_alias_and_no_persistence_access(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/SearchQueryResolutionOwnerSourceRuntimeReadServiceProvider.php');
        self::assertSame(1, preg_match_all('/->alias\(DeterministicSearchQueryResolutionOwnerSourceRuntimeReadV1::class, SearchQueryResolutionOwnerSourceRuntimeReadV1::class\)/', $contents));
        self::assertStringContainsString('singleton', $contents);
        foreach (['PostgreSql', 'Mapper', 'PDO', 'DatabaseManager', 'RuntimeHealth', 'OwnerReader'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_migration_076_remains_frozen(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/076_search_query_resolution_owner_source.sql');
        self::assertSame('4439ef5640e9349b50aeb8e7166c66ac4fb05d6ae41b94e90c9cec0467a963b2', hash('sha256', $migration));
    }
}
