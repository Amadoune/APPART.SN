<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PlaceLifecycleHttpRuntimeArchitectureTest extends TestCase
{
    public function test_route_is_unique_and_uuid_constrained(): void
    {
        $routes = (string) file_get_contents(dirname(__DIR__, 2).'/routes/web.php');

        self::assertSame(1, substr_count(
            $routes,
            "Route::post('/api/place-lifecycles/{placeId}/transitions'",
        ));
        self::assertSame(1, substr_count($routes, "->name('place-lifecycle.transition')"));
        self::assertStringContainsString("->whereUuid('placeId')", $routes);
    }

    public function test_controller_delegates_once_and_contains_no_business_or_persistence_dependency(): void
    {
        $controller = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Http/Controllers/PlaceLifecycleHttpController.php',
        );

        self::assertSame(1, substr_count($controller, '->transition('));
        self::assertSame(1, substr_count($controller, '->applicationRequest('));
        foreach ([
            'PlaceLifecycleWorkflow',
            'PlaceLifecycleWorkflowStore',
            'PlaceLifecycleEventCatalog',
            'PublicProjectionOutboxWriter',
            'PDO',
            'PostgreSql',
            'beginTransaction',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $controller);
        }
    }

    public function test_mapper_has_no_default_and_covers_every_closed_status_once(): void
    {
        $mapper = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Http/PlaceLifecycleHttpResultMapper.php',
        );

        self::assertStringNotContainsString('default', $mapper);
        foreach ([
            'Applied',
            'AlreadyApplied',
            'WorkflowRefused',
            'InspectionMissing',
            'InspectionCorrupted',
            'ContextDivergence',
            'ReplayConflict',
            'SourceVersionConflict',
            'TargetVersionConflict',
            'StateConflict',
            'TransitionRejected',
        ] as $status) {
            self::assertSame(1, substr_count(
                $mapper,
                'PlaceLifecycleOrchestrationStatus::'.$status,
            ), $status);
        }
        self::assertSame(1, substr_count($mapper, "'atomic_integration_failure'"));
    }

    public function test_http_does_not_modify_certified_migrations_or_create_a_parallel_runtime(): void
    {
        $root = dirname(__DIR__, 2);
        foreach (['038_place_lifecycle_workflow.sql', '039_place_lifecycle_event_inbox.sql', '040_geography_outbox_owner.sql'] as $migration) {
            self::assertStringNotContainsString(
                'PlaceLifecycleHttp',
                (string) file_get_contents(match (substr($migration, 0, 3)) {
                    '038' => $root.'/src/Modules/Geography/Infrastructure/Persistence/PostgreSql/Migrations/'.$migration,
                    '039' => $root.'/app/Infrastructure/PlaceLifecycleEventRouting/PostgreSql/Migrations/'.$migration,
                    '040' => $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/'.$migration,
                }),
            );
        }
    }
}
