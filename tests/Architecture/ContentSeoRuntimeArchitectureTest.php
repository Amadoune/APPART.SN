<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ContentSeoRuntimeArchitectureTest extends TestCase
{
    public function test_application_runtime_depends_only_on_the_owner_port(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Application/Runtime';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $contents = (string) file_get_contents($file->getPathname());
            foreach (['PDO', 'Illuminate\\', 'Infrastructure\\', 'PostgreSql', 'Mapper', 'RuntimeRead', 'Reader', 'RuntimeHealth', 'App\\Http', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Transport', 'Routing', 'SQL'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file->getPathname());
            }
        }
    }

    public function test_provider_bindings_are_unique_singletons(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/ContentSeoRuntimeServiceProvider.php');
        self::assertSame(1, preg_match_all('/->alias\(PostgreSqlContentSeoOwnerSource::class, ContentSeoOwnerSource::class\)/', $provider));
        self::assertSame(1, preg_match_all('/->alias\(DeterministicContentSeoRuntime::class, ContentSeoRuntimeV1::class\)/', $provider));
        self::assertStringContainsString('singleton', $provider);
        foreach (['ListingLifecycle', 'SearchDiscovery', 'IdentityAccess', 'RuntimeHealth', 'Http', 'Event', 'Delivery', 'Outbox', 'Consumer', 'Transport', 'Routing'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_migration_078_is_frozen(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/078_editorial_content_operational_seo_owner_source.sql');
        self::assertSame('10892a3813dbb0f85d33be014c4b3a1077dd6d89321f66551451fb673d8a1f9f', hash('sha256', $migration));
    }
}
