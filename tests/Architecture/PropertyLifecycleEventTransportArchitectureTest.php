<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class PropertyLifecycleEventTransportArchitectureTest extends TestCase
{
    public function test_foundation_contains_one_payload_adapter_and_one_router_port(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/PropertyLifecycleEventTransport';
        self::assertCount(5, glob($root.'/*.php') ?: []);
        self::assertFileExists($root.'/PropertyLifecycleDeliveryPayload.php');
        self::assertFileExists($root.'/Contract/PropertyLifecycleEventRouter.php');
        self::assertSame(1, substr_count((string) file_get_contents($root.'/PropertyLifecycleDeliveryPayload.php'), 'implements PublicProjectionDeliveryPayload'));
    }

    public function test_transport_contains_no_business_decision_or_event_creation(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/PropertyLifecycleEventTransport';
        foreach (array_merge(glob($root.'/*.php') ?: [], glob($root.'/Contract/*.php') ?: []) as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['PropertyLifecycleWorkflow', '->decide(', 'PropertyLifecycleTransition', 'eventsFor('] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_transport_has_no_runtime_infrastructure_or_delivery_side_effect(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/PropertyLifecycleEventTransport';
        foreach (array_merge(glob($root.'/*.php') ?: [], glob($root.'/Contract/*.php') ?: []) as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Illuminate', 'PDO', 'PostgreSql', 'OutboxWriter', 'ServiceProvider', 'Consumer', 'Worker', 'Http', 'ProjectionUpdater', 'ProjectionStore', 'append(', 'publish(', 'dispatch('] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
        }
    }

    public function test_transport_contract_does_not_depend_on_later_consumer_mapper_or_destination(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = (string) file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertStringNotContainsString('PropertyLifecycleDeliveryPayload', $provider);
        foreach (array_merge(glob($root.'/app/Application/PropertyLifecycleEventTransport/*.php') ?: [], glob($root.'/app/Application/PropertyLifecycleEventTransport/Contract/*.php') ?: []) as $file) {
            $contents = (string) file_get_contents($file);
            self::assertStringNotContainsString('PropertyLifecycleEventDeliveryConsumer', $contents);
            self::assertStringNotContainsString('PostgreSqlPublicProjectionOutboxMapper', $contents);
            self::assertStringNotContainsString('PropertyLifecycleEventDestination', $contents);
        }
    }
}
