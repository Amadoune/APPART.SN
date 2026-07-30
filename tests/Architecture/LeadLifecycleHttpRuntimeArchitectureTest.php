<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleHttpRuntimeArchitectureTest extends TestCase
{
    public function test_controller_delegates_once_only_to_atomic_integrator(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/LeadLifecycleHttpController.php');
        self::assertSame(1, substr_count($contents, '$this->orchestrator->transition('));
        foreach (['LeadLifecycleWorkflow', 'TransitionStore', 'ReplayInspector', 'PostgreSql', 'PDO', 'Outbox', 'Worker', 'Consumer', 'Router', 'Projection', 'ListingCatalog', 'AdvertiserCatalog'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_request_performs_transport_validation_and_translation_only(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Requests/LeadLifecycleTransitionRequest.php');
        foreach (['LeadLifecycleWorkflow', 'LeadLifecycleState', 'LeadEligibilityProof', 'ListingCatalog', 'AdvertiserCatalog', 'Repository', 'PostgreSql', 'PDO', 'Outbox', 'now(', 'Carbon', 'random'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
        self::assertSame(1, substr_count($contents, 'new LeadLifecycleAtomicEventRequest('));
        self::assertStringContainsString('LeadLifecycleAction::Unknown', $contents);
    }

    public function test_result_mapper_is_exhaustive_without_default(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/LeadLifecycleHttpResultMapper.php');
        self::assertSame(8, substr_count($contents, 'LeadLifecycleOrchestrationStatus::'));
        self::assertStringNotContainsString('default', $contents);
    }

    public function test_route_is_unique_and_infrastructure_free(): void
    {
        $routes = (string) file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        self::assertSame(1, substr_count($routes, "Route::post('/api/lead-lifecycles/{leadId}/transitions'"));
        self::assertSame(1, substr_count($routes, "->whereUuid('leadId')"));
        self::assertStringNotContainsString('LeadLifecycleWorkflow', $routes);
        self::assertStringNotContainsString('PostgreSql', $routes);
    }
}
