<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleOutboxCompatibilityArchitectureTest extends TestCase
{
    public function test_catalog_and_mapper_extend_the_single_existing_points(): void
    {
        $root = dirname(__DIR__, 2);
        $catalog = (string) file_get_contents($root.'/app/Application/PublicProjectionDelivery/PublicProjectionDeliveryEventCatalog.php');
        $mapper = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxMapper.php');

        self::assertSame(1, substr_count($catalog, 'foreach (MediaItemLifecycleEventType::cases()'));
        self::assertSame(1, substr_count($catalog, "'Media', 'MediaItemLifecycle', MediaItemLifecycleDeliveryPayload::class"));
        self::assertSame(1, substr_count($mapper, 'MediaItemLifecycleDeliveryPayload::restore($data)'));
    }

    public function test_consumer_contains_no_workflow_outbox_or_business_logic(): void
    {
        $consumer = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/MediaItemLifecycleEventConsumption/MediaItemLifecycleDeliveryConsumer.php');

        self::assertSame(1, substr_count($consumer, '->route('));
        self::assertSame(1, substr_count($consumer, '->consumptionFor('));
        foreach (['Workflow', 'PDO', 'Outbox', 'beginTransaction', 'MediaItemLifecycleAction', 'MediaCollection'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $consumer);
        }
    }

    public function test_worker_registry_has_one_closed_registration_loop(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        self::assertSame(1, substr_count($provider, 'singleton(MediaItemLifecycleDeliveryConsumer::class)'));
        self::assertSame(1, substr_count($provider, '$mediaItemLifecycleConsumer = $app->make(MediaItemLifecycleDeliveryConsumer::class)'));
        self::assertSame(1, substr_count($provider, 'foreach (MediaItemLifecycleEventType::cases()'));
    }

    public function test_frozen_migrations_and_transport_remain_free_of_compatibility(): void
    {
        $root = dirname(__DIR__, 2);

        self::assertStringNotContainsString('public_projection_outbox', (string) file_get_contents($root.'/app/Infrastructure/MediaItemLifecycleEventRouting/PostgreSql/Migrations/033_media_item_lifecycle_event_inbox.sql'));
        self::assertStringNotContainsString('PublicProjectionOutbox', (string) file_get_contents($root.'/app/Application/MediaItemLifecycleEventTransport/MediaItemLifecycleDeliveryPayload.php'));
        self::assertSame([], glob($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/034_*') ?: []);
    }
}
