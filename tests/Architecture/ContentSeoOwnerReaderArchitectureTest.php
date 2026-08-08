<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ContentSeoOwnerReaderArchitectureTest extends TestCase
{
    public function test_owner_readers_depend_only_on_owner_source_and_public_contracts(): void
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root.'/src/Modules/ContentSeo/Application/OwnerReader/**/*.php');
        $files = array_merge($files, glob($root.'/src/Modules/ContentSeo/Application/OwnerReader/*.php'));
        $php = implode('', array_map(static fn (string $file): string => (string) file_get_contents($file), $files));
        foreach (['ContentSeoOwnerSource', 'EditorialContentReaderV1', 'OperationalSeoReaderV1'] as $allowed) {
            self::assertStringContainsString($allowed, $php);
        }
        foreach (['Application\\Runtime', 'Http\\', 'PostgreSql', 'Mapper', 'PDO', 'Infrastructure\\', 'Event\\', 'Delivery\\', 'Outbox\\', 'Consumer', 'Routing', 'Transport', 'SQL'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $php);
        }
    }

    public function test_provider_exposes_unique_singleton_aliases(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/ContentSeoOwnerReaderServiceProvider.php');
        foreach (['ContentSeoOwnerReaderPolicy', 'EditorialContentOwnerReader', 'OperationalSeoOwnerReader'] as $binding) {
            self::assertSame(1, substr_count($provider, 'singleton('.$binding.'::class'));
        }
        foreach (['ContentSeoOwnerReaderV1', 'EditorialContentReaderV1', 'OperationalSeoReaderV1'] as $contract) {
            self::assertSame(1, substr_count($provider, ', '.$contract.'::class)'));
        }
        foreach (['PostgreSql', 'Mapper', 'Runtime', 'Http', 'Event', 'Delivery', 'Outbox'] as $forbidden) {
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
