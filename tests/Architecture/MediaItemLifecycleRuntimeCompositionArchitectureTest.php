<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class MediaItemLifecycleRuntimeCompositionArchitectureTest extends TestCase
{
    public function test_existing_runtime_root_contains_one_lazy_graph(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'singleton(MediaItemLifecycleWorkflow::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(MediaItemLifecycleWorkflowMapper::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(PostgreSqlMediaItemLifecycleWorkflowRepository::class)'));
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlMediaItemLifecycleWorkflowRepository::class, MediaItemLifecycleWorkflowStore::class)'));
        self::assertSame([], glob($root.'/app/Providers/*MediaItemLifecycle*') ?: []);
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
        self::assertStringContainsString('RuntimeHealthComponent::MediaItemLifecycleWorkflow, MediaItemLifecycleWorkflow::class', $requirements);
        self::assertStringContainsString('RuntimeHealthComponent::MediaItemLifecycleWorkflowStore, MediaItemLifecycleWorkflowStore::class', $requirements);
        foreach (['PostgreSqlMediaItemLifecycleWorkflowRepository', 'MediaItemLifecycleWorkflowMapper', '->decide(', '->read(', '->append('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $requirements);
        }
    }

    public function test_composition_introduces_no_collection_context_or_unauthorized_downstream_layer(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        $lines = array_filter(explode("\n", $provider), static fn (string $line): bool => str_contains($line, 'MediaItemLifecycle'));
        $composition = implode("\n", $lines);
        foreach (['MediaCollectionRegistry', 'Http', 'Worker', 'Fake', 'Null', 'Fallback'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $composition);
        }
    }
}
