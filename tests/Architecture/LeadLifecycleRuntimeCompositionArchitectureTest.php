<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class LeadLifecycleRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_existing_runtime_root_contains_one_lazy_lead_graph(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'singleton(LeadLifecycleWorkflow::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(LeadLifecycleWorkflowMapper::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlLeadLifecycleWorkflowRepository::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlLeadLifecycleWorkflowRepository::class, LeadLifecycleWorkflowStore::class)'));
        self::assertSame([], glob($root.'/app/Providers/*LeadLifecycle*') ?: []);
    }

    public function test_bootstrap_executes_no_workflow_storage_transaction_or_sql(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        foreach (['->decide(', '->initialize(', '->append(', '->read(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
        self::assertDoesNotMatchRegularExpression('/\b(?:SELECT|INSERT|UPDATE|DELETE)\b/', $provider);
    }

    public function test_runtime_health_only_inspects_the_two_application_capabilities(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertStringContainsString('RuntimeHealthComponent::LeadLifecycleWorkflow, LeadLifecycleWorkflow::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::LeadLifecycleWorkflowStore, LeadLifecycleWorkflowStore::class', $requirements);
        foreach (['PostgreSqlLeadLifecycleWorkflowRepository', 'LeadLifecycleWorkflowMapper', '->decide(', '->read(', '->append('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $requirements);
        }
    }

    public function test_composition_introduces_no_eligibility_http_outbox_worker_event_projection_or_fake(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        $leadLines = array_filter(explode("\n", $provider), static fn (string $line): bool => str_contains($line, 'LeadLifecycleWorkflow') || str_contains($line, 'PostgreSqlLeadLifecycleWorkflowRepository'));
        $composition = implode("\n", $leadLines);
        foreach (['ListingCatalog', 'AdvertiserCatalog', 'Http', 'Outbox', 'Worker', 'Consumer', 'Event', 'Projection', 'Fake', 'Null', 'Fallback'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $composition);
        }
    }
}
