<?php

namespace Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ReservationLifecycleEventTransportArchitectureTest extends TestCase
{
    public function test_foundation_contains_the_five_transport_contracts_and_two_authorized_outcome_contracts(): void
    {
        $files = glob($this->transportRoot().'/*.php') ?: [];
        self::assertCount(7, $files);
        foreach ([
            'ReservationLifecycleDeliveryPayload.php',
            'ReservationLifecycleDeliveryMetadata.php',
            'ReservationLifecycleTransportSerializer.php',
            'ReservationLifecycleTransportEnvelope.php',
            'ReservationLifecycleEventRouterPort.php',
            'ReservationLifecycleRoutingResult.php',
            'ReservationLifecycleRoutingStatus.php',
        ] as $file) {
            self::assertFileExists($this->transportRoot().'/'.$file);
        }
        self::assertSame(1, substr_count((string) file_get_contents($this->transportRoot().'/ReservationLifecycleDeliveryPayload.php'), 'implements PublicProjectionDeliveryPayload'));
    }

    public function test_router_port_returns_the_closed_result_without_any_implementation(): void
    {
        $port = (string) file_get_contents($this->transportRoot().'/ReservationLifecycleEventRouterPort.php');
        self::assertStringContainsString('): ReservationLifecycleRoutingResult;', $port);
        self::assertSame(0, substr_count($port, '): void;'));

        foreach (glob($this->transportRoot().'/*.php') ?: [] as $file) {
            self::assertDoesNotMatchRegularExpression(
                '/class\s+\w+\s+implements\s+ReservationLifecycleEventRouterPort/',
                (string) file_get_contents($file),
                $file,
            );
        }
    }

    public function test_transport_contains_no_business_decision_or_event_production(): void
    {
        foreach (glob($this->transportRoot().'/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['ReservationLifecycleWorkflow', '->decide(', 'ReservationLifecycleEventCatalog', 'eventFor(', 'append(', 'publish(', 'dispatch('] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $contents, $file);
            }
        }
    }

    public function test_transport_has_no_infrastructure_runtime_or_delivery_implementation(): void
    {
        foreach (glob($this->transportRoot().'/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['Illuminate', 'Laravel', 'PDO', 'PostgreSql', 'Outbox', 'Consumer', 'Worker', 'Inbox', 'RabbitMQ', 'Kafka', 'Http', 'ProjectionUpdater', 'ProjectionStore', 'ServiceProvider'] as $forbidden) {
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
            self::assertDoesNotMatchRegularExpression('/class\s+\w+\s+implements\s+ReservationLifecycleEventRouterPort/', $contents, $file);
        }
    }

    public function test_transport_payload_remains_unwired_while_the_port_is_explicitly_composed(): void
    {
        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/app/Providers/PublicProjectionRuntimeServiceProvider.php');
        $orchestrator = (string) file_get_contents(dirname(__DIR__, 2).'/src/Modules/ReservationLifecycle/Application/ReservationLifecycleOrchestration/DeterministicReservationLifecycleEventOrchestrator.php');

        foreach (['ReservationLifecycleDeliveryPayload', 'ReservationLifecycleTransportEnvelope'] as $contract) {
            self::assertStringNotContainsString($contract, $provider);
            self::assertStringNotContainsString($contract, $orchestrator);
        }
        self::assertStringContainsString('ReservationLifecycleEventRouterPort', $provider);
        self::assertStringNotContainsString('ReservationLifecycleEventRouterPort', $orchestrator);
    }

    public function test_transport_contains_no_clock_random_identity_or_event_id_derivation(): void
    {
        foreach (glob($this->transportRoot().'/*.php') ?: [] as $file) {
            $contents = (string) file_get_contents($file);
            foreach (['now()', 'new DateTime', 'CURRENT_TIMESTAMP', 'clock_timestamp', 'microtime(', 'time()', 'random', 'uuid(', 'ReservationLifecycleEventId::derive'] as $forbidden) {
                if (str_ends_with($file, 'ReservationLifecycleDeliveryPayload.php') && $forbidden === 'ReservationLifecycleEventId::derive') {
                    continue;
                }
                self::assertStringNotContainsStringIgnoringCase($forbidden, $contents, $file);
            }
        }

        $payload = (string) file_get_contents($this->transportRoot().'/ReservationLifecycleDeliveryPayload.php');
        self::assertSame(1, substr_count($payload, 'ReservationLifecycleEventId::derive'));
        self::assertStringContainsString("if (\$eventId->value !== self::string(\$data, 'eventId'))", $payload);
    }

    private function transportRoot(): string
    {
        return dirname(__DIR__, 2).'/app/Application/ReservationLifecycleEventTransport';
    }
}
