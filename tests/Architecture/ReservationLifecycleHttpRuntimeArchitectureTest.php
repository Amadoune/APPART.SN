<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReservationLifecycleHttpRuntimeArchitectureTest extends TestCase
{
    public function test_controller_delegates_once_only_to_atomic_integrator(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/ReservationLifecycleHttpController.php');
        self::assertSame(1, substr_count($contents, '$this->orchestrator->transition('));
        foreach (['ReservationLifecycleWorkflow', 'WorkflowStore', 'PostgreSql', 'PDO', 'Outbox', 'Worker', 'Consumer', 'Router', 'Projection'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_request_contains_only_transport_validation_and_translation(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Requests/ReservationLifecycleTransitionRequest.php');
        foreach (['ReservationLifecycleWorkflow', 'ReservationLifecycleState', 'new ReservationLifecycleTransition(', 'Repository', 'PostgreSql', 'PDO', 'Outbox', 'now(', 'Carbon', 'random'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
        self::assertSame(1, substr_count($contents, 'new ReservationLifecycleAtomicEventRequest('));
        self::assertStringContainsString('ReservationLifecycleAction::Unknown', $contents);
    }

    public function test_result_mapper_is_exhaustive_without_default(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/ReservationLifecycleHttpResultMapper.php');
        self::assertSame(7, substr_count($contents, 'ReservationLifecycleOrchestrationStatus::'));
        self::assertStringNotContainsString('default', $contents);
    }

    public function test_route_is_unique_and_contains_no_business_or_persistence_dependency(): void
    {
        $routes = (string) file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        self::assertSame(1, substr_count($routes, "Route::post('/api/reservation-lifecycles/{reservationId}/transitions'"));
        self::assertSame(1, substr_count($routes, "->whereUuid('reservationId')"));
        self::assertStringNotContainsString('ReservationLifecycleWorkflow', $routes);
        self::assertStringNotContainsString('PostgreSql', $routes);
    }
}
