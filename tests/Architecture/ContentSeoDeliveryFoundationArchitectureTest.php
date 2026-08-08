<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ContentSeoDeliveryFoundationArchitectureTest extends TestCase
{
    public function test_delivery_depends_only_on_content_seo_events_v1(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Application/Delivery';
        $php = '';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $php .= (string) file_get_contents($file->getPathname());
            }
        }
        self::assertStringContainsString('EditorialContentEventV1', $php);
        self::assertStringContainsString('OperationalSeoEventV1', $php);
        self::assertSame(2, substr_count($php, 'public function create('));
        foreach (['OwnerSource', 'Reader', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Provider', 'SQL', 'Transport', 'Routing', 'Consumer', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_migration_078_remains_frozen(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('10892a3813dbb0f85d33be014c4b3a1077dd6d89321f66551451fb673d8a1f9f', hash_file('sha256', $root.'078_editorial_content_operational_seo_owner_source.sql'));
        self::assertSame('7f4b34eb7277c5d51e1577961c2429af34c1f7a7f71f647359cb8080e153ff7a', hash_file('sha256', $root.'078_editorial_content_operational_seo_owner_source.down.sql'));
    }
}
