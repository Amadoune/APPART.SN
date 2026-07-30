<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyLifecycleRuntimeOrchestrationArchitectureTest extends TestCase
{
    public function test_orchestrator_only_coordinates_the_certified_workflow_and_store(): void
    {
        $orchestrator = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Application/PropertyLifecycle/DeterministicPropertyLifecycleOrchestrator.php');
        self::assertSame(1, substr_count($orchestrator, '$this->store->read('));
        self::assertSame(1, substr_count($orchestrator, '$this->workflow->decide('));
        self::assertSame(1, substr_count($orchestrator, '$this->store->append('));
        foreach (['TRANSITIONS', 'new PropertyLifecycleTransition', 'PropertyLifecycleState::', 'PropertyLifecycleAction::'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $orchestrator);
        }
    }

    public function test_orchestrator_has_no_http_postgresql_outbox_event_projection_or_aggregate_dependency(): void
    {
        $orchestrator = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Application/PropertyLifecycle/DeterministicPropertyLifecycleOrchestrator.php');
        foreach (['Illuminate', 'Http', 'PDO', 'PostgreSql', 'Outbox', 'Worker', 'Consumer', 'Event', 'Projection', 'Aggregate'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $orchestrator);
        }
    }

    public function test_runtime_root_contains_one_lazy_orchestrator_alias_without_execution(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'singleton(DeterministicPropertyLifecycleOrchestrator::class)'));
        self::assertSame(1, substr_count($provider, 'alias(DeterministicPropertyLifecycleOrchestrator::class, PropertyLifecycleOrchestrator::class)'));
        foreach (['->transition(', '->decide(', '->append(', '->read(', '->beginTransaction('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_runtime_health_inspects_the_orchestrator_contract_without_functional_call(): void
    {
        $requirements = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/RuntimeHealth/PublicProjectionRuntimeRequirements.php');
        self::assertStringContainsString('RuntimeHealthComponent::PropertyLifecycleOrchestrator, PropertyLifecycleOrchestrator::class', $requirements);
        self::assertStringNotContainsString('DeterministicPropertyLifecycleOrchestrator', $requirements);
        self::assertStringNotContainsString('->transition(', $requirements);
    }
}
