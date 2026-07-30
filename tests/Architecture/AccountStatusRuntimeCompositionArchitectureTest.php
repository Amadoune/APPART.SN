<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class AccountStatusRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_bindings_and_health_capabilities_are_unique(): void
    {
        $provider = $this->provider();

        self::assertSame(1, substr_count($provider, 'singleton(AccountStatusWorkflow::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(AccountStatusWorkflowMapper::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlAccountStatusWorkflowStore::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlAccountStatusWorkflowStore::class, AccountStatusWorkflowStore::class)'));
        self::assertSame(1, substr_count($provider, 'RuntimeHealthComponent::AccountStatusWorkflow, AccountStatusWorkflow::class'));
        self::assertSame(1, substr_count($provider, 'RuntimeHealthComponent::AccountStatusWorkflowStore, AccountStatusWorkflowStore::class'));
    }

    public function test_runtime_health_catalog_is_additive_at_fifty_seven(): void
    {
        $root = dirname(__DIR__, 2);
        $components = (string) file_get_contents($root.'/app/Application/RuntimeHealth/RuntimeHealthComponent.php');
        $requirements = (string) file_get_contents($root.'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');

        self::assertGreaterThanOrEqual(57, substr_count($components, 'case '));
        self::assertGreaterThanOrEqual(57, substr_count($requirements, 'new RuntimeHealthRequirement('));
        self::assertSame(1, substr_count($components, "case AccountStatusWorkflow = 'account_status_workflow';"));
        self::assertSame(1, substr_count($components, "case AccountStatusWorkflowStore = 'account_status_workflow_store';"));
    }

    public function test_composition_contains_no_execution_or_future_layer(): void
    {
        $provider = $this->provider();
        $lines = array_filter(
            explode("\n", $provider),
            static fn (string $line): bool => str_contains($line, 'AccountStatus'),
        );
        $composition = implode("\n", $lines);

        foreach ([
            '->read(', '->bootstrap(', '->append(', 'beginTransaction(',
            'Http',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $composition);
        }
    }

    public function test_orchestration_bindings_are_additive_and_unique(): void
    {
        $provider = $this->provider();

        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlAccountStatusOrchestrationTransaction::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlAccountStatusOrchestrationTransaction::class, AccountStatusOrchestrationTransaction::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(DeterministicAccountStatusOrchestrator::class)'));
        self::assertSame(1, substr_count($provider, 'alias(DeterministicAccountStatusOrchestrator::class, AccountStatusOrchestrator::class)'));
        self::assertSame(1, substr_count($provider, 'RuntimeHealthComponent::AccountStatusOrchestrator, AccountStatusOrchestrator::class'));
    }

    private function provider(): string
    {
        return (string) file_get_contents(
            dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php',
        );
    }
}
