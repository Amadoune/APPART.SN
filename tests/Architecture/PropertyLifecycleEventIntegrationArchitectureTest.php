<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyLifecycleEventIntegrationArchitectureTest extends TestCase
{
    public function test_integrator_only_coordinates_certified_components(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/app/Application/PropertyLifecycleEventIntegration/AtomicPropertyLifecycleEventOrchestrator.php');
        self::assertSame(1, substr_count($contents, '$this->orchestrator->transition('));
        self::assertSame(1, substr_count($contents, '$this->events->eventsFor('));
        self::assertSame(1, substr_count($contents, '$this->outbox->append('));
        self::assertSame(1, substr_count($contents, '$this->transaction->run('));
        foreach (['PropertyLifecycleWorkflow', 'PropertyLifecycleState::', 'PropertyLifecycleAction::', 'new PropertyLifecycleEvent(', 'eventId->', 'Http', 'ProjectionUpdater', 'ProjectionStore', 'EventRouter', 'ConsumerRegistry', 'PublicProjectionDeliveryWorker'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_existing_postgresql_transaction_implements_both_atomic_ports_without_parallel_strategy(): void
    {
        $root = dirname(__DIR__, 2);
        $contents = (string) file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlAggregateOutboxTransaction.php');
        self::assertStringContainsString('ListingPublicationAtomicTransaction', $contents);
        self::assertStringContainsString('PropertyLifecycleAtomicTransaction', $contents);
        self::assertSame([], glob($root.'/app/Infrastructure/**/*PropertyLifecycle*Transaction.php') ?: []);
    }

    public function test_runtime_composes_one_lazy_event_orchestrator_without_execution(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlAggregateOutboxTransaction::class, PropertyLifecycleAtomicTransaction::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(AtomicPropertyLifecycleEventOrchestrator::class)'));
        self::assertSame(1, substr_count($provider, 'alias(AtomicPropertyLifecycleEventOrchestrator::class, PropertyLifecycleEventOrchestrator::class)'));
        foreach (['->transition(', '->eventsFor(', '->append(', '->run('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_certified_property_orchestrator_remains_event_free(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Application/PropertyLifecycle/DeterministicPropertyLifecycleOrchestrator.php');
        foreach (['PropertyLifecycleEvent', 'Outbox', 'PublicProjectionDelivery', 'PropertyLifecycleEventMetadata'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_integration_has_no_http_projection_worker_consumer_or_router_dependency(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/PropertyLifecycleEventIntegration';
        foreach (array_merge(glob($root.'/*.php') ?: [], glob($root.'/Contract/*.php') ?: []) as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Illuminate', 'Http', 'Controller', 'ProjectionUpdater', 'ProjectionStore', 'PublicProjectionDeliveryWorker', 'ConsumerRegistry', 'PropertyLifecycleEventRouter'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }
}
