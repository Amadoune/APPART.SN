<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReservationLifecycleOutboxCompatibilityArchitectureTest extends TestCase
{
    public function test_consumer_depends_only_on_delivery_transport_router_and_policy(): void
    {
        $consumer = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/ReservationLifecycleEventConsumer/ReservationLifecycleDeliveryConsumer.php');

        foreach (['Workflow', 'PostgreSql', 'PDO', 'OutboxWriter', 'ProjectionStore', 'Http\\'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $consumer);
        }
        self::assertStringContainsString('ReservationLifecycleEventRouterPort', $consumer);
        self::assertStringContainsString('ReservationLifecycleDeliveryConsumptionPolicy', $consumer);
        self::assertSame(1, substr_count($consumer, '->route('));
        self::assertSame(1, substr_count($consumer, '->consumptionFor('));
    }

    public function test_later_atomic_and_http_layers_add_no_producer_projection_or_specific_worker(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['ReservationLifecycleEventProducer', 'ReservationLifecycleDeliveryWorker'] as $class) {
            self::assertSame([], glob($root.'/app/**/*'.$class.'*.php') ?: []);
        }
        self::assertSame(1, substr_count((string) file_get_contents($root.'/routes/web.php'), "Route::post('/api/reservation-lifecycles/{reservationId}/transitions'"));
    }

    public function test_runtime_health_contract_contains_all_certified_capabilities(): void
    {
        $components = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/RuntimeHealthComponent.php');
        self::assertGreaterThanOrEqual(55, substr_count($components, 'case '));
        self::assertStringContainsString("case PlaceLifecycleWorkflow = 'place_lifecycle_workflow';", $components);
        self::assertStringContainsString("case PlaceLifecycleWorkflowStore = 'place_lifecycle_workflow_store';", $components);
        self::assertStringContainsString("case PlaceLifecycleDeliveryConsumer = 'place_lifecycle_delivery_consumer';", $components);
    }
}
