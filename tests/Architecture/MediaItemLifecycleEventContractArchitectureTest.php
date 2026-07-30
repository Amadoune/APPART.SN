<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleEventContractArchitectureTest extends TestCase
{
    public function test_event_foundation_is_closed_confidential_and_infrastructure_free(): void
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root.'/src/Modules/Media/Application/MediaItemLifecycleEvent/*.php') ?: [];
        self::assertCount(8, $files);
        $source = implode("\n", array_map(static fn (string $file): string => (string) file_get_contents($file), $files));

        foreach (['PostgreSql', 'PDO', 'Laravel', 'Illuminate', 'Repository', 'Migration', 'Transport', 'Inbox', 'Outbox', 'Consumer', 'Worker', 'Http', 'MediaCollectionRegistry', 'MediaCollectionRepository', 'replacementMediaId', 'collectionId', 'caption', 'contentChecksum', 'now(', 'random'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_no_runtime_binding_publication_or_downstream_layer_is_introduced(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertStringNotContainsString('MediaItemLifecycleEventCatalog::class', $provider);
        self::assertStringNotContainsString('MediaItemLifecycleEventSerializer::class', $provider);
        self::assertSame([], glob($root.'/app/**/*MediaItemLifecycle*Event*.php') ?: []);
    }

    public function test_catalog_is_bijective_for_the_two_certified_transitions(): void
    {
        $catalog = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/Media/Application/MediaItemLifecycleEvent/MediaItemLifecycleEventCatalog.php');
        self::assertSame(1, substr_count($catalog, 'MediaItemLifecycleEventType::Removed'));
        self::assertSame(1, substr_count($catalog, 'MediaItemLifecycleEventType::Archived'));
    }
}
