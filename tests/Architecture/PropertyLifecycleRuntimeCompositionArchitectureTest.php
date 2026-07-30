<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyLifecycleRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_existing_runtime_root_contains_one_lazy_property_lifecycle_graph(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'singleton(PropertyLifecycleWorkflow::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PropertyLifecycleWorkflowMapper::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlPropertyLifecycleWorkflowRepository::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlPropertyLifecycleWorkflowRepository::class, PropertyLifecycleWorkflowStore::class)'));
        self::assertSame([], glob($root.'/app/Providers/*PropertyLifecycle*') ?: []);
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
        self::assertStringContainsString('RuntimeHealthComponent::PropertyLifecycleWorkflow, PropertyLifecycleWorkflow::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::PropertyLifecycleWorkflowStore, PropertyLifecycleWorkflowStore::class', $requirements);
        foreach (['PostgreSqlPropertyLifecycleWorkflowRepository', 'PropertyLifecycleWorkflowMapper', '->decide(', '->read(', '->append('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $requirements);
        }
    }

    public function test_composition_introduces_no_http_outbox_worker_event_projection_or_fake(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        $propertyLines = array_filter(explode("\n", $provider), static fn (string $line): bool => str_contains($line, 'PropertyLifecycleWorkflow') || str_contains($line, 'DeterministicPropertyLifecycleOrchestrator'));
        $composition = implode("\n", $propertyLines);
        foreach (['Http', 'Outbox', 'Worker', 'Consumer', 'Event', 'Projection', 'Fake', 'Null', 'Fallback'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $composition);
        }
    }
}
