<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ListingPublicationHttpRuntimeArchitectureTest extends TestCase
{
    public function test_controller_calls_only_the_certified_event_orchestrator(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/ListingPublicationTransitionController.php');
        self::assertIsString($controller);
        self::assertSame(1, substr_count($controller, '$this->orchestrator->transition('));
        foreach (['ListingPublicationWorkflow', 'ListingPublicationWorkflowStore', 'PostgreSql', 'PDO', 'Outbox', 'Worker', 'Consumer', 'Projection'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $controller);
        }
    }

    public function test_request_contains_only_transport_validation_and_application_translation(): void
    {
        $request = file_get_contents(dirname(__DIR__, 2).'/app/Http/Requests/ListingPublicationTransitionHttpRequest.php');
        self::assertIsString($request);
        foreach (['ListingPublicationWorkflow', 'ListingPublicationState', 'new ListingPublicationTransition(', 'Repository', 'PostgreSql', 'PDO', 'Outbox', 'now(', 'Carbon', 'random'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $request);
        }
        self::assertSame(1, substr_count($request, 'new ListingPublicationEventOrchestrationRequest('));
    }

    public function test_http_mapping_is_closed_without_default_branch(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/ListingPublicationTransitionController.php');
        self::assertIsString($controller);
        self::assertSame(5, substr_count($controller, 'ListingPublicationOrchestrationStatus::'));
        self::assertStringNotContainsString('default', $controller);
    }

    public function test_route_exposes_only_the_http_adapter(): void
    {
        $routes = file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        self::assertIsString($routes);
        self::assertSame(1, substr_count($routes, "Route::post('/api/listing-publications/{listingId}/transitions'"));
        self::assertSame(1, substr_count($routes, "->whereUuid('listingId')"));
        self::assertStringNotContainsString('ListingPublicationWorkflow', $routes);
        self::assertStringNotContainsString('PostgreSql', $routes);
    }
}
