<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceLifecycleOutboxCompatibilityArchitectureTest extends TestCase
{
    public function test_catalog_mapper_and_schema_extend_only_the_generic_points(): void
    {
        $root = dirname(__DIR__, 2);
        $catalog = (string) file_get_contents($root.'/app/Application/PublicProjectionDelivery/PublicProjectionDeliveryEventCatalog.php');
        $mapper = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxMapper.php');
        $schema = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxSchema.php');

        self::assertSame(1, substr_count($catalog, 'foreach (PlaceLifecycleEventType::cases()'));
        self::assertSame(1, substr_count($catalog, "'Geography', 'PlaceLifecycle', PlaceLifecycleDeliveryPayload::class"));
        self::assertSame(1, substr_count($mapper, 'PlaceLifecycleDeliveryPayload::restore($data)'));
        self::assertSame(1, substr_count($schema, "'Geography' => 'geography'"));
        self::assertSame(1, substr_count($schema, "'geography' => 'Geography'"));
    }

    public function test_worker_uses_the_existing_consumer_once_for_three_closed_event_types(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        self::assertSame(1, substr_count($provider, 'singleton(PlaceLifecycleDeliveryConsumer::class)'));
        self::assertSame(1, substr_count($provider, '$placeLifecycleConsumer = $app->make(PlaceLifecycleDeliveryConsumer::class)'));
        self::assertSame(1, substr_count($provider, 'foreach (PlaceLifecycleEventType::cases()'));
    }

    public function test_no_specialized_outbox_component_or_frozen_migration_change_is_introduced(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['Writer', 'Reader', 'Mapper', 'Worker'] as $suffix) {
            self::assertFileDoesNotExist($root.'/app/Infrastructure/PlaceLifecycleOutbox/PlaceLifecycleOutbox'.$suffix.'.php');
        }
        self::assertStringNotContainsString(
            'public_projection_outbox',
            (string) file_get_contents($root.'/app/Infrastructure/PlaceLifecycleEventRouting/PostgreSql/Migrations/039_place_lifecycle_event_inbox.sql'),
        );
        self::assertStringNotContainsString(
            'public_projection_outbox',
            (string) file_get_contents($root.'/src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/038_place_lifecycle_workflow.sql'),
        );
    }
}
