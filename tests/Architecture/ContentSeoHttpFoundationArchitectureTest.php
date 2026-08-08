<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ContentSeoHttpFoundationArchitectureTest extends TestCase
{
    public function test_http_depends_only_on_public_v1_readers(): void
    {
        $root = dirname(__DIR__, 2);
        $files = array_merge(glob($root.'/app/Http/Controllers/*ContentController.php'), glob($root.'/app/Http/Controllers/OperationalSeoController.php'), glob($root.'/app/Http/Requests/*ContentRequest.php'), glob($root.'/app/Http/Requests/OperationalSeoRequest.php'), glob($root.'/app/Http/ContentSeo/*.php'));
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        foreach (['EditorialContentReaderV1', 'OperationalSeoReaderV1'] as $reader) {
            self::assertStringContainsString($reader, $php);
        }
        foreach (['ContentSeoOwnerSource', 'Application\\Runtime', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Event\\', 'Delivery\\', 'Outbox\\', 'SQL'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_registers_only_http_components(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/ContentSeoHttpServiceProvider.php');
        foreach (['ContentSeoResponseFactory', 'EditorialContentController', 'OperationalSeoController'] as $binding) {
            self::assertSame(1, substr_count($provider, 'singleton('.$binding.'::class'));
        }
        foreach (['ContentSeoOwnerSource', 'PostgreSql', 'Mapper', 'RuntimeServiceProvider', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_migration_078_remains_frozen(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/ContentSeo/Infrastructure/Persistence/PostgreSql/Migrations/';
        self::assertSame('10892a3813dbb0f85d33be014c4b3a1077dd6d89321f66551451fb673d8a1f9f', hash_file('sha256', $root.'078_editorial_content_operational_seo_owner_source.sql'));
        self::assertSame('7f4b34eb7277c5d51e1577961c2429af34c1f7a7f71f647359cb8080e153ff7a', hash_file('sha256', $root.'078_editorial_content_operational_seo_owner_source.down.sql'));
    }
}
