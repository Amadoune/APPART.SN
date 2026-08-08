<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SearchOwnerSourceRuntimeArchitectureTest extends TestCase
{
    public function test_application_runtime_depends_only_on_the_owner_port(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Application/SearchOwnerSourceRuntime';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'Mapper', 'Reader', 'RuntimeHealth', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_provider_bindings_are_unique_singletons(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/SearchOwnerSourceRuntimeServiceProvider.php');
        self::assertSame(1, preg_match_all('/->alias\(PostgreSqlSearchOwnerSource::class, SearchOwnerSource::class\)/', $contents));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicSearchOwnerSourceRuntimeV1::class, SearchOwnerSourceRuntimeV1::class\)/', $contents));
        self::assertStringContainsString('singleton', $contents);
        foreach (['ListingLifecycle', 'PublicProjection', 'ContentSeo', 'RuntimeHealth', 'Http', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_persistence_migration_is_frozen(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/SearchDiscovery/Infrastructure/Persistence/PostgreSql/Migrations/075_search_owner_source.sql');
        self::assertSame('0b4f54833eb452fe944ec82f5468ea329b42b4d5d0b4aebaef38200ed00a65dc', hash('sha256', $migration));
    }
}
