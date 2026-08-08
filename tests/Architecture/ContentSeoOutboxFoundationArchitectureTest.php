<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ContentSeoOutboxFoundationArchitectureTest extends TestCase
{
    public function test_outbox_depends_only_on_content_seo_deliveries(): void
    {
        $roots = [dirname(__DIR__, 2).'/src/Modules/ContentSeo/Application/Outbox', dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Outbox'];
        $php = '';
        foreach ($roots as $root) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $php .= (string) file_get_contents($file->getPathname());
                }
            }
        }
        self::assertStringContainsString('EditorialContentDeliveryV1', $php);
        self::assertStringContainsString('OperationalSeoDeliveryV1', $php);
        foreach (['OwnerSource', 'ReaderV1', 'Application\\Runtime', 'RuntimeRead', 'App\\Http', 'Mapper', 'Provider', 'Transport', 'Routing', 'Consumer'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_repository_is_the_only_postgresql_enclave_and_migration_078_is_frozen(): void
    {
        $root = dirname(__DIR__, 2);
        $repository = (string) file_get_contents($root.'/src/Modules/ContentSeo/Infrastructure/Outbox/PostgreSqlContentSeoOutboxRepository.php');
        self::assertStringContainsString('PDO', $repository);
        self::assertStringNotContainsString('ContentSeoOwnerSource', $repository);
        $migration = $root.'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('10892a3813dbb0f85d33be014c4b3a1077dd6d89321f66551451fb673d8a1f9f', hash_file('sha256', $migration.'078_editorial_content_operational_seo_owner_source.sql'));
        self::assertSame('7f4b34eb7277c5d51e1577961c2429af34c1f7a7f71f647359cb8080e153ff7a', hash_file('sha256', $migration.'078_editorial_content_operational_seo_owner_source.down.sql'));
    }
}
