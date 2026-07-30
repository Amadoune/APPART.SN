<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ListingPublicationEventTransportArchitectureTest extends TestCase
{
    public function test_transport_foundation_contains_one_payload_adapter_and_one_router_port(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/ListingPublicationEventTransport';
        $files = glob($root.'/*.php') ?: [];
        self::assertCount(5, $files);
        self::assertFileExists($root.'/ListingPublicationDeliveryPayload.php');
        self::assertFileExists($root.'/Contract/ListingPublicationEventRouter.php');
        self::assertSame(1, substr_count((string) file_get_contents($root.'/ListingPublicationDeliveryPayload.php'), 'implements PublicProjectionDeliveryPayload'));
    }

    public function test_transport_contains_no_business_decision_or_event_creation(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/ListingPublicationEventTransport';
        foreach (array_merge(glob($root.'/*.php') ?: [], glob($root.'/Contract/*.php') ?: []) as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['ListingPublicationWorkflow', '->decide(', 'ListingPublicationTransition', 'eventsFor('] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_transport_has_no_runtime_infrastructure_or_delivery_side_effect(): void
    {
        $root = dirname(__DIR__, 2).'/app/Application/ListingPublicationEventTransport';
        foreach (array_merge(glob($root.'/*.php') ?: [], glob($root.'/Contract/*.php') ?: []) as $file) {
            $contents = file_get_contents($file);
            self::assertIsString($contents);
            foreach (['Illuminate', 'PDO', 'PostgreSql', 'OutboxWriter', 'ServiceProvider', 'Consumer', 'Worker', 'Http', 'ProjectionUpdater', 'ProjectionStore', 'append(', 'publish(', 'dispatch('] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
        }
    }

    public function test_no_binding_consumer_mapper_or_provider_is_added(): void
    {
        $root = dirname(__DIR__, 2);
        $provider = file_get_contents($root.'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        self::assertIsString($provider);
        self::assertStringNotContainsString('ListingPublicationDeliveryPayload', $provider);
        self::assertSame([], glob($root.'/app/**/*ListingPublication*Consumer.php') ?: []);
        self::assertSame([], glob($root.'/app/**/*ListingPublication*Mapper.php') ?: []);
    }
}
