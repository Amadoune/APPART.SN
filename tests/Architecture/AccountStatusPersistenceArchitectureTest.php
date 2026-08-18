<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AccountStatusPersistenceArchitectureTest extends TestCase
{
    public function test_persistence_slice_contains_only_the_certified_components(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/IdentityAccess';

        self::assertFileExists($root.'/Application/AccountStatusPersistence/Contract/AccountStatusWorkflowStore.php');
        self::assertFileExists($root.'/Infrastructure/Persistence/AccountStatusWorkflowMapper.php');
        self::assertFileExists($root.'/Infrastructure/Persistence/PostgreSql/PostgreSqlAccountStatusWorkflowStore.php');
        self::assertFileExists($root.'/Infrastructure/Persistence/PostgreSql/Migrations/041_account_status_lifecycle_workflow.sql');
        self::assertFileExists($root.'/Infrastructure/Persistence/PostgreSql/Migrations/041_account_status_lifecycle_workflow.down.sql');
    }

    public function test_store_never_mutates_the_historical_account_or_calls_its_status_methods(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/PostgreSqlAccountStatusWorkflowStore.php',
        );

        foreach (['->save(', '->suspend(', '->reactivate(', 'SuspendAccount', 'ReactivateAccount'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
        self::assertStringContainsString('$this->accounts->find(', $source);
    }

    public function test_persistence_does_not_decide_transitions_or_import_future_layers(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/IdentityAccess';
        $files = [
            ...(glob($root.'/Application/AccountStatusPersistence/**/*.php') ?: []),
            ...(glob($root.'/Application/AccountStatusPersistence/*.php') ?: []),
            $root.'/Infrastructure/Persistence/AccountStatusWorkflowMapper.php',
            $root.'/Infrastructure/Persistence/PostgreSql/PostgreSqlAccountStatusWorkflowStore.php',
        ];
        foreach ($files as $file) {
            self::assertFileExists($file);
        }
        $source = implode("\n", array_map(static fn (string $file): string => (string) file_get_contents($file), $files));

        foreach ([
            'AccountStatusWorkflow(', 'AlreadyInState', 'InvalidContext',
            'Inspector', 'Orchestrator', 'RuntimeHealth', 'Event', 'Transport',
            'Routing', 'Outbox', 'Http', 'Worker', 'Consumer',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source, $forbidden);
        }
    }

    public function test_migration_is_additive_reversible_and_enforces_the_closed_machine(): void
    {
        $root = dirname(__DIR__, 2).'/src/Modules/IdentityAccess/Infrastructure/Persistence/PostgreSql/Migrations/';
        $up = (string) file_get_contents($root.'041_account_status_lifecycle_workflow.sql');
        $down = (string) file_get_contents($root.'041_account_status_lifecycle_workflow.down.sql');

        self::assertStringContainsString('CREATE SCHEMA IF NOT EXISTS identity_access', $up);
        self::assertStringContainsString("('active','suspend','suspended')", $up);
        self::assertStringContainsString("('suspended','reactivate','active')", $up);
        self::assertStringContainsString('version >= 0', $up);
        self::assertStringContainsString('DROP TABLE IF EXISTS identity_access.account_status_lifecycle_transitions', $down);
        self::assertStringNotContainsString('038_', $up.$down);
        self::assertStringNotContainsString('039_', $up.$down);
        self::assertStringNotContainsString('040_', $up.$down);
    }

    public function test_certified_workflow_and_historical_contracts_remain_independent(): void
    {
        $root = dirname(__DIR__, 2);
        $workflow = (string) file_get_contents($root.'/src/Modules/IdentityAccess/Application/AccountStatusLifecycle/AccountStatusWorkflow.php');
        $account = (string) file_get_contents($root.'/src/Modules/IdentityAccess/Domain/Model/Account.php');
        $registry = (string) file_get_contents($root.'/src/Modules/IdentityAccess/Application/Contract/AccountRegistry.php');

        self::assertStringNotContainsString('AccountStatusPersistence', $workflow);
        self::assertStringNotContainsString('AccountStatusPersistence', $account);
        self::assertStringNotContainsString('AccountStatusWorkflowStore', $registry);
    }
}
