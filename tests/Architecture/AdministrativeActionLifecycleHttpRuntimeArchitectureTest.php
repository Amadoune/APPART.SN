<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecycleHttpRuntimeArchitectureTest extends TestCase
{
    public function test_controller_is_only_an_atomic_http_adapter(): void
    {
        $source = $this->source('app/Http/Controllers/AdministrativeActionLifecycleHttpController.php');

        self::assertStringContainsString('AdministrativeActionLifecycleAtomicEventOrchestrator', $source);
        self::assertSame(1, substr_count($source, '->transition('));
        foreach ([
            'AdministrativeActionLifecycleWorkflow',
            'AdministrativeActionContextualTransitionStore',
            'AdministrativeActionContextualReplayInspector',
            'PublicProjectionOutbox',
            'EventRouter',
            'PDO',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_request_only_transports_explicit_certified_context(): void
    {
        $source = $this->source('app/Http/Requests/AdministrativeActionLifecycleTransitionRequest.php');

        foreach (['::record(', '::approve(', '::reject('] as $factory) {
            self::assertSame(1, substr_count($source, $factory));
        }
        foreach ([
            'AdministrativeActionLifecycleWorkflow',
            'AdministrativeActionContextualTransitionStore',
            'FourEyesPolicy',
            'now(',
            'random_',
            'PublicProjectionOutbox',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    public function test_mapper_exhaustively_names_all_nine_results_without_default(): void
    {
        $source = $this->source('app/Http/AdministrativeActionLifecycleHttpResultMapper.php');

        foreach ([
            'Applied',
            'AlreadyApplied',
            'Missing',
            'VersionConflict',
            'Denied',
            'StateConflict',
            'ContextDivergence',
            'TransitionDivergence',
            'PersistenceCorrupted',
        ] as $status) {
            self::assertSame(1, substr_count(
                $source,
                "AdministrativeActionLifecycleOrchestrationStatus::$status",
            ));
        }
        self::assertStringNotContainsString('default', $source);
    }

    public function test_route_is_unique_and_no_certified_migration_mentions_http(): void
    {
        $routes = $this->source('routes/web.php');
        self::assertSame(1, substr_count(
            $routes,
            '/api/administrative-action-lifecycles/{actionId}/transitions',
        ));

        $root = dirname(__DIR__, 2);
        foreach ([
            'src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/034_administrative_action_lifecycle_workflow.sql',
            'src/Modules/AdministrationAudit/Infrastructure/Persistence/PostgreSql/Migrations/035_administrative_action_lifecycle_context.sql',
            'app/Infrastructure/AdministrativeActionLifecycleEventRouting/PostgreSql/Migrations/036_administrative_action_lifecycle_event_inbox.sql',
            'app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/037_administration_audit_outbox_owner.sql',
        ] as $file) {
            self::assertStringNotContainsString(
                'AdministrativeActionLifecycleHttpController',
                (string) file_get_contents($root.'/'.$file),
            );
        }
    }

    private function source(string $path): string
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/'.$path);
        self::assertIsString($source);

        return $source;
    }
}
