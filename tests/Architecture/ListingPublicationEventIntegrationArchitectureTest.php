<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ListingPublicationEventIntegrationArchitectureTest extends TestCase
{
    public function test_event_orchestrator_only_coordinates_certified_components(): void
    {
        $file = dirname(__DIR__, 2).'/app/Application/ListingPublicationEventIntegration/AtomicListingPublicationEventOrchestrator.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertSame(1, substr_count($contents, '$this->orchestrator->transition('));
        self::assertSame(1, substr_count($contents, '$this->events->eventsFor('));
        self::assertSame(1, substr_count($contents, '$this->outbox->append('));
        self::assertSame(1, substr_count($contents, '$this->transaction->run('));
        foreach (['ListingPublicationWorkflow', 'ListingPublicationState::', 'ListingPublicationAction::', 'new ListingPublicationEvent(', 'eventId->', 'Http', 'ProjectionUpdater', 'ProjectionStore'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_existing_postgresql_transaction_is_reused_without_parallel_strategy(): void
    {
        $root = dirname(__DIR__, 2);
        $transaction = file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlAggregateOutboxTransaction.php');
        self::assertIsString($transaction);
        self::assertStringContainsString('ListingPublicationAtomicTransaction', $transaction);
        self::assertSame([], glob($root.'/app/Infrastructure/**/*ListingPublication*Transaction.php') ?: []);
    }

    public function test_runtime_composes_one_lazy_event_orchestrator_without_execution(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        self::assertSame(1, substr_count($provider, 'alias(PostgreSqlAggregateOutboxTransaction::class, ListingPublicationAtomicTransaction::class)'));
        self::assertSame(1, substr_count($provider, 'singleton(AtomicListingPublicationEventOrchestrator::class)'));
        self::assertSame(1, substr_count($provider, 'alias(AtomicListingPublicationEventOrchestrator::class, ListingPublicationEventOrchestrator::class)'));
        foreach (['->transition(', '->eventsFor(', '->append(', '->run('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $provider);
        }
    }

    public function test_certified_orchestrator_remains_unchanged_and_event_free(): void
    {
        $orchestrator = file_get_contents(dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/PublicationWorkflow/DeterministicListingPublicationOrchestrator.php');
        self::assertIsString($orchestrator);
        foreach (['ListingPublicationEvent', 'Outbox', 'PublicProjectionDelivery', 'ListingPublicationEventMetadata'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $orchestrator);
        }
    }

    public function test_integration_has_no_http_projection_worker_consumer_or_router_implementation(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/ListingPublicationEventIntegration';
        foreach (array_merge(glob($root.'/*.php') ?: [], glob($root.'/Contract/*.php') ?: []) as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'Http', 'Controller', 'ProjectionUpdater', 'ProjectionStore', 'PublicProjectionDeliveryWorker', 'ConsumerRegistry', 'ListingPublicationEventRouter'] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }
}
