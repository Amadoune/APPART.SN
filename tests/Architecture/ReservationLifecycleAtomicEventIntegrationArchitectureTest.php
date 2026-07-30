<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReservationLifecycleAtomicEventIntegrationArchitectureTest extends TestCase
{
    public function test_integrator_coordinates_only_certified_application_contracts(): void
    {
        $root = dirname(__DIR__, 2);
        $integrator = (string) file_get_contents($root.'/app/Application/ReservationLifecycleEventIntegration/ReservationLifecycleAtomicEventOrchestrator.php');

        foreach (['PDO', 'PostgreSql', 'Http\\', 'Controller', 'ProjectionStore', 'Worker', 'DeliveryConsumer', 'EventRouter'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $integrator);
        }
        self::assertStringContainsString('ReservationLifecycleEventOrchestrator', $integrator);
        self::assertStringContainsString('ReservationLifecycleAtomicTransaction', $integrator);
        self::assertStringContainsString('ReservationLifecycleEventCatalog', $integrator);
        self::assertStringContainsString('PublicProjectionOutboxWriter', $integrator);
        self::assertSame(1, substr_count($integrator, '->eventFor('));
        self::assertSame(1, substr_count($integrator, '->append('));
        self::assertStringNotContainsString('new ReservationLifecycleTransition', $integrator);
    }

    public function test_generic_catalog_mapper_consumer_and_worker_contract_are_not_modified_by_atomic_integration(): void
    {
        $root = dirname(__DIR__, 2);
        foreach ([
            $root.'/app/Application/PublicProjectionDelivery/PublicProjectionDeliveryEventCatalog.php',
            $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxMapper.php',
            $root.'/app/Application/ReservationLifecycleEventConsumer/ReservationLifecycleDeliveryConsumer.php',
            $root.'/app/Application/PublicProjectionWorker/PublicProjectionDeliveryWorker.php',
        ] as $file) {
            self::assertStringNotContainsString('ReservationLifecycleAtomicEventOrchestrator', (string) file_get_contents($file), $file);
            self::assertStringNotContainsString('ReservationLifecycleAtomicTransaction', (string) file_get_contents($file), $file);
        }
    }

    public function test_no_http_projection_broker_or_specific_worker_is_created(): void
    {
        $root = dirname(__DIR__, 2);
        self::assertStringNotContainsString('ReservationLifecycleAtomicEventOrchestrator', (string) file_get_contents($root.'/routes/web.php'));
        self::assertFileDoesNotExist($root.'/app/Http/Controllers/ReservationLifecycleController.php');
        self::assertFileDoesNotExist($root.'/app/Application/ReservationLifecycleEventIntegration/ReservationLifecycleWorker.php');
        self::assertFileDoesNotExist($root.'/app/Application/ReservationLifecycleEventIntegration/ReservationLifecycleProjection.php');
    }
}
