<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AccountStatusHttpRuntimeArchitectureTest extends TestCase
{
    public function test_only_suspend_and_reactivate_routes_are_exposed_and_secured(): void
    {
        $routes = (string) file_get_contents(dirname(__DIR__, 2).'/routes/web.php');

        self::assertSame(1, substr_count($routes, "'/api/account-statuses/{accountId}/suspend'"));
        self::assertSame(1, substr_count($routes, "'/api/account-statuses/{accountId}/reactivate'"));
        self::assertSame(2, substr_count($routes, "->whereUuid('accountId')"));
        self::assertSame(1, substr_count($routes, 'RequireAccountStatusLifecycleAuthority::class'));
        self::assertSame(2, substr_count($routes, 'ValidateCsrfToken::class'));
    }

    public function test_controller_calls_only_the_atomic_application_boundary(): void
    {
        $controller = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Http/Controllers/AccountStatusHttpController.php',
        );

        self::assertSame(1, substr_count($controller, '$this->orchestrator->transition('));
        foreach ([
            'AccountStatusWorkflow',
            'AccountStatusWorkflowStore',
            'AccountRegistry',
            'AccountStatusEventRouter',
            'PublicProjectionOutbox',
            'PostgreSql',
            'Repository',
            'PDO',
            'beginTransaction',
            'publish(',
            'dispatch(',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $controller);
        }
    }

    public function test_presenter_has_no_default_and_covers_eight_results_once(): void
    {
        $presenter = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Http/AccountStatusHttpResultPresenter.php',
        );

        self::assertStringNotContainsString('default', $presenter);
        foreach ([
            'Applied',
            'AlreadyInState',
            'AccountMissing',
            'VersionConflict',
            'InvalidContext',
            'PersistenceRejected',
            'PersistenceCorrupted',
            'InspectionCorrupted',
        ] as $status) {
            self::assertSame(1, substr_count(
                $presenter,
                'AccountStatusOrchestrationStatus::'.$status,
            ), $status);
        }
    }

    public function test_http_request_excludes_sensitive_and_infrastructure_contracts(): void
    {
        $request = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Http/Requests/AccountStatusTransitionHttpRequest.php',
        );
        $middleware = (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Http/Middleware/RequireAccountStatusLifecycleAuthority.php',
        );

        foreach ([
            'Credential',
            'VerificationToken',
            'RoleId',
            'Consent',
            'HistoricalAccount',
            'PublicProjectionDelivery',
            'routingProof',
            'destination',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $request);
        }
        self::assertStringNotContainsString('actorId', $middleware);
        self::assertStringContainsString('hash_equals', $middleware);
        self::assertStringContainsString('account_status_authorized', $middleware);
    }

    public function test_http_creates_no_migration_or_parallel_runtime(): void
    {
        $root = dirname(__DIR__, 2);
        foreach ([
            $root.'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/041_account_status_lifecycle_workflow.sql',
            $root.'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/042_historical_account_persistence.sql',
            $root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/Migrations/043_identity_access_outbox_owner.sql',
        ] as $migration) {
            self::assertStringNotContainsString(
                'AccountStatusHttp',
                (string) file_get_contents($migration),
            );
        }
    }
}
