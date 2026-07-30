<?php

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MediaIngestionEventDeliveryArchitectureTest extends TestCase
{
    #[Test]
    public function event_contract_and_delivery_are_framework_and_persistence_free(): void
    {
        $roots = [
            dirname(__DIR__, 2).'/src/Modules/Media/Application/MediaIngestionEvent',
            dirname(__DIR__, 2).'/app/Application/MediaIngestionEventTransport',
            dirname(__DIR__, 2).'/app/Application/MediaIngestionEventRouting',
            dirname(__DIR__, 2).'/app/Application/MediaIngestionEventDelivery',
        ];
        foreach ($roots as $root) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $source = (string) file_get_contents($file->getPathname());
                foreach (['PDO', 'PostgreSql', 'Migration', 'Illuminate\\', 'Controller', 'Middleware', 'Outbox', 'SELECT ', 'objectKey', 'filename'] as $forbidden) {
                    self::assertStringNotContainsString($forbidden, $source);
                }
            }
        }
    }

    #[Test]
    public function catalog_has_exactly_three_types_and_provider_is_additive(): void
    {
        $root = dirname(__DIR__, 2);
        $type = (string) file_get_contents($root.'/src/Modules/Media/Application/MediaIngestionEvent/MediaIngestionEventType.php');
        self::assertSame(3, substr_count($type, 'case '));
        $providers = (string) file_get_contents($root.'/bootstrap/providers.php');
        self::assertStringContainsString('MediaIngestionEventDeliveryServiceProvider::class', $providers);
        $provider = (string) file_get_contents($root.'/app/Providers/MediaIngestionEventDeliveryServiceProvider.php');
        foreach (['Http', 'Controller', 'Route::', 'Middleware', 'Outbox', 'PDO'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    #[Test]
    public function frozen_migrations_and_runtime_health_are_unchanged(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertSame(60, substr_count($requirements, 'new RuntimeHealthRequirement('));
        self::assertFileExists(dirname(__DIR__, 2).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/058_media_ingestion.sql');
        self::assertFileExists(dirname(__DIR__, 2).'/src/Modules/Media/Infrastructure/Persistence/PostgreSql/Migrations/059_media_attachment_intents.sql');
    }
}
