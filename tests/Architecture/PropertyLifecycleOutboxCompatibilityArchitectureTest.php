<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyLifecycleOutboxCompatibilityArchitectureTest extends TestCase
{
    public function test_existing_catalog_is_extended_once_from_the_certified_event_enum(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Application/PublicProjectionDelivery/PublicProjectionDeliveryEventCatalog.php');
        self::assertIsString($contents);
        self::assertSame(1, substr_count($contents, 'PropertyLifecycleEventType::cases()'));
        self::assertSame(1, substr_count($contents, 'PropertyLifecycleDeliveryPayload::class'));
        foreach (['PropertyLifecycleWorkflow', 'PropertyLifecycleTransition', '->decide('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }

    public function test_existing_mapper_restores_transport_without_parallel_mapper(): void
    {
        $root = dirname(__DIR__, 2);
        $mapper = file_get_contents($root.'/app/Infrastructure/PublicProjectionOutbox/PostgreSql/PostgreSqlPublicProjectionOutboxMapper.php');
        self::assertIsString($mapper);
        self::assertSame(1, substr_count($mapper, 'PropertyLifecycleDeliveryPayload::restore($data)'));
        self::assertSame(1, substr_count($mapper, 'PropertyLifecycleEventType::tryFrom($eventType)'));
        self::assertSame([], glob($root.'/app/Infrastructure/**/*PropertyLifecycle*OutboxMapper.php') ?: []);
    }

    public function test_consumer_only_restores_routes_and_maps_closed_outcomes(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/app/Application/PropertyLifecycleEventConsumer/PropertyLifecycleEventDeliveryConsumer.php');
        self::assertIsString($contents);
        self::assertStringContainsString('PropertyLifecycleDeliveryPayload::restore(', $contents);
        self::assertSame(1, substr_count($contents, '$this->router->route($event)'));
        foreach (['PropertyLifecycleWorkflow', 'PropertyLifecycleTransition', '->decide(', 'new PropertyLifecycleEvent', 'Http', 'ProjectionUpdater', 'ProjectionStore'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
        self::assertDoesNotMatchRegularExpression('/\bdefault\s*=>/', $contents);
    }

    public function test_runtime_registers_one_consumer_and_all_seven_types_without_execution(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        self::assertSame(1, substr_count($provider, 'singleton(PropertyLifecycleEventDeliveryConsumer::class)'));
        self::assertSame(1, substr_count($provider, 'foreach (PropertyLifecycleEventType::cases() as $eventType)'));
        self::assertStringNotContainsString('->consume(', $provider);
        self::assertStringNotContainsString('->route(', $provider);
    }

    public function test_orchestrator_still_produces_no_event_or_outbox_message(): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/src/Modules/RealEstateCatalog/Application/PropertyLifecycle/DeterministicPropertyLifecycleOrchestrator.php');
        self::assertIsString($contents);
        foreach (['PropertyLifecycleEvent', 'PropertyLifecycleDeliveryPayload', 'Outbox', 'publish(', 'dispatch('] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $contents);
        }
    }
}
