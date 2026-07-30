<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ListingPublicationOutboxCompatibilityArchitectureTest extends TestCase
{
    public function test_existing_catalog_is_extended_from_the_certified_event_enum_once(): void
    {
        $file = dirname(__DIR__, 2).'/app/Application/PublicProjectionDelivery/PublicProjectionDeliveryEventCatalog.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertSame(1, substr_count($contents, 'ListingPublicationEventType::cases()'));
        self::assertSame(1, substr_count($contents, 'ListingPublicationDeliveryPayload::class'));
        foreach (['ListingPublicationWorkflow', 'ListingPublicationTransition', '->decide('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_existing_mapper_restores_the_certified_transport_without_duplication(): void
    {
        $root = dirname(__DIR__, 2);
        $mapper = file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxMapper.php');
        self::assertIsString($mapper);
        self::assertSame(1, substr_count($mapper, 'ListingPublicationDeliveryPayload::restore($data)'));
        self::assertSame(1, substr_count($mapper, 'ListingPublicationEventType::tryFrom($eventType)'));
        self::assertSame([], glob($root.'/app/Infrastructure/**/*ListingPublication*OutboxMapper.php') ?: []);
    }

    public function test_transport_consumer_only_restores_routes_and_maps_closed_outcomes(): void
    {
        $file = dirname(__DIR__, 2).'/app/Application/ListingPublicationEventConsumer/ListingPublicationEventDeliveryConsumer.php';
        $contents = file_get_contents($file);
        self::assertIsString($contents);
        self::assertStringContainsString('ListingPublicationDeliveryPayload::restore(', $contents);
        self::assertSame(1, substr_count($contents, '$this->router->route($event)'));
        foreach (['ListingPublicationWorkflow', 'ListingPublicationTransition', '->decide(', 'new ListingPublicationEvent', 'Http', 'ProjectionUpdater', 'ProjectionStore'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
        self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents);
    }

    public function test_runtime_registers_one_consumer_and_all_types_without_execution(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        self::assertSame(1, substr_count($provider, 'singleton(ListingPublicationEventDeliveryConsumer::class)'));
        self::assertSame(1, substr_count($provider, 'foreach (ListingPublicationEventType::cases() as $eventType)'));
        self::assertStringNotContainsString('->consume(', $provider);
        self::assertStringNotContainsString('->route(', $provider);
    }

    public function test_orchestrator_still_emits_no_event_or_outbox_message(): void
    {
        $orchestrator = file_get_contents(dirname(__DIR__, 2).'/src/Modules/ListingLifecycle/Application/PublicationWorkflow/DeterministicListingPublicationOrchestrator.php');
        self::assertIsString($orchestrator);
        foreach (['ListingPublicationEvent', 'ListingPublicationDeliveryPayload', 'Outbox', 'publish(', 'dispatch('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $orchestrator);
        }
    }
}
