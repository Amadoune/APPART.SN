<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AccountStatusWorkflowArchitectureTest extends TestCase
{
    public function test_foundation_contains_only_the_certified_workflow_models(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Application/AccountStatusLifecycle';
        $files = glob($root.'/*.php') ?: [];

        self::assertSame([
            'AccountStatusAction.php',
            'AccountStatusActorId.php',
            'AccountStatusContextV1.php',
            'AccountStatusContextVersion.php',
            'AccountStatusCurrentState.php',
            'AccountStatusDecision.php',
            'AccountStatusIntentId.php',
            'AccountStatusOccurredAt.php',
            'AccountStatusState.php',
            'AccountStatusTransition.php',
            'AccountStatusVersion.php',
            'AccountStatusWorkflow.php',
            'AccountStatusWorkflowResult.php',
        ], array_map('basename', $files));
    }

    public function test_workflow_foundation_has_no_forbidden_dependency_or_decision(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Application/AccountStatusLifecycle';
        $source = implode("\n", array_map(
            static fn (string $file): string => (string) file_get_contents($file),
            glob($root.'/*.php') ?: [],
        ));

        foreach ([
            'AccountRegistry', 'Domain\\Model\\Account', 'Inspection', 'Inspector',
            'AlreadyApplied', 'ReplayConflict', 'InspectionCorrupted',
            'AccountMissing', 'VersionConflict', 'PersistenceRejected',
            'Repository', 'Persistence', 'PDO', 'PostgreSql', 'Migration',
            'Illuminate\\', 'Laravel', 'Runtime', 'Event', 'Transport',
            'Routing', 'Outbox', 'Inbox', 'Http', 'Worker', 'Consumer',
            'Projection', 'Transaction', 'RoleAssignment', 'Credential',
            'Verification', 'Consent', 'Session', 'now(', 'time(', 'random',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source, $forbidden);
        }
    }

    public function test_decision_table_is_closed_without_default_or_business_exception(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Application/AccountStatusLifecycle/AccountStatusWorkflow.php',
        );

        self::assertStringNotContainsString('default', $source);
        self::assertStringNotContainsString('throw ', $source);
        self::assertStringNotContainsString('catch ', $source);
        self::assertSame(2, substr_count($source, 'AccountStatusDecision::AlreadyInState'));
        self::assertSame(1, substr_count($source, 'AccountStatusDecision::InvalidContext'));
        self::assertSame(2, substr_count($source, 'AccountStatusWorkflowResult::applied('));
    }

    public function test_frozen_account_foundations_remain_independent(): void
    {
        $root = dirname(__DIR__, 2);
        $account = (string) file_get_contents($root.'/src/Modules/IdentityAccess/Domain/Model/Account.php');
        $registry = (string) file_get_contents($root.'/src/Modules/IdentityAccess/Application/Contract/AccountRegistry.php');

        self::assertStringNotContainsString('AccountStatusLifecycle', $account);
        self::assertStringNotContainsString('AccountStatusWorkflow', $registry);
        self::assertSame([], glob($root.'/src/Modules/IdentityAccess/Infrastructure/*AccountStatus*') ?: []);
        self::assertSame([], glob($root.'/database/migrations/*account_status*') ?: []);
    }
}
