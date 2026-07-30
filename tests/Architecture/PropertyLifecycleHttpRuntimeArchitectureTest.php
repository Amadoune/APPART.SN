<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyLifecycleHttpRuntimeArchitectureTest extends TestCase
{
    public function test_controller_calls_only_the_certified_event_orchestrator(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/PropertyLifecycleTransitionController.php');
        self::assertSame(1, substr_count($contents, '$this->orchestrator->transition('));
        foreach (['PropertyLifecycleWorkflow', 'PropertyLifecycleWorkflowStore', 'PostgreSql', 'PDO', 'Outbox', 'Worker', 'Consumer', 'Router', 'Projection'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_form_request_contains_only_transport_validation_and_application_translation(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Requests/PropertyLifecycleTransitionHttpRequest.php');
        foreach (['PropertyLifecycleWorkflow', 'PropertyLifecycleState', 'new PropertyLifecycleTransition(', 'Repository', 'PostgreSql', 'PDO', 'Outbox', 'now(', 'Carbon', 'random'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
        self::assertSame(1, substr_count($contents, 'new PropertyLifecycleEventOrchestrationRequest('));
        self::assertStringContainsString('PropertyLifecycleAction::Unknown', $contents);
    }

    public function test_http_mapping_is_closed_without_default_branch(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/PropertyLifecycleTransitionController.php');
        self::assertSame(5, substr_count($contents, 'PropertyLifecycleOrchestrationStatus::'));
        self::assertStringNotContainsString('default', $contents);
    }

    public function test_route_exposes_one_property_lifecycle_command_adapter(): void
    {
        $routes = (string) file_get_contents(dirname(__DIR__, 2).'/routes/web.php');
        self::assertSame(1, substr_count($routes, "Route::post('/api/property-lifecycles/{propertyId}/transitions'"));
        self::assertSame(1, substr_count($routes, "->whereUuid('propertyId')"));
        self::assertStringNotContainsString('PropertyLifecycleWorkflow', $routes);
        self::assertStringNotContainsString('PostgreSql', $routes);
    }
}
