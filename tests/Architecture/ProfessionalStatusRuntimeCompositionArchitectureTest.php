<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ProfessionalStatusRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_existing_runtime_root_contains_one_lazy_professional_status_graph(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        self::assertSame(1, substr_count($provider, 'singleton(ProfessionalStatusWorkflow::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(ProfessionalStatusWorkflowMapper::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlProfessionalStatusWorkflowRepository::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlProfessionalStatusWorkflowRepository::class, ProfessionalStatusWorkflowStore::class)'));
        self::assertSame([], glob($root.'/app/Providers/*ProfessionalStatus*') ?: []);
    }

    public function test_bootstrap_executes_no_workflow_storage_transaction_or_sql(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');

        foreach (['->decide(', '->initialize(', '->append(', '->read(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }

        self::assertDoesNotMatchRegularExpression('/\b(?:SELECT|INSERT|UPDATE|DELETE)\b/', $provider);
    }

    public function test_runtime_health_exposes_only_the_two_application_capabilities(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');

        self::assertStringContainsString('RuntimeHealthComponent::ProfessionalStatusWorkflow, ProfessionalStatusWorkflow::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::ProfessionalStatusWorkflowStore, ProfessionalStatusWorkflowStore::class', $requirements);

        foreach (['PostgreSqlProfessionalStatusWorkflowRepository', 'ProfessionalStatusWorkflowMapper', '->decide(', '->read(', '->append('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $requirements);
        }
    }

    public function test_composition_introduces_no_context_orchestration_http_event_or_delivery_dependency(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        $lines = array_filter(explode("\n", $provider), static fn (string $line): bool => str_contains($line, 'ProfessionalStatusWorkflow::class')
            || str_contains($line, 'ProfessionalStatusWorkflowMapper::class')
            || str_contains($line, 'PostgreSqlProfessionalStatusWorkflowRepository::class')
            || str_contains($line, 'ProfessionalStatusWorkflowStore::class'));
        $composition = implode("\n", $lines);

        foreach (['Actor', 'occurredAt', 'Orchestrator', 'Http', 'Event', 'Inbox', 'Outbox', 'Worker', 'Consumer', 'Eligibility', 'Fake', 'Null', 'Fallback'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $composition);
        }
    }
}
