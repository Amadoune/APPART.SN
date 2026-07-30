<?php

namespace Tests\Unit\PropertyLifecycleEventTransport;

use App\Application\PropertyLifecycleEventTransport\Contract\PropertyLifecycleEventRouter;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleDeliveryPayload;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventRoutingDiagnosticCode;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventRoutingResult;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventRoutingStatus;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventTransportException;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryIdempotencyKey;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEvent;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventCatalog;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventInstant;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventMetadata;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventSerializer;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class PropertyLifecycleEventTransportContractTest extends TestCase
{
    public function test_unique_adapter_implements_existing_delivery_payload_contract(): void
    {
        $payload = new PropertyLifecycleDeliveryPayload($this->event());

        self::assertInstanceOf(PublicProjectionDeliveryPayload::class, $payload);
        self::assertTrue((new ReflectionClass($payload))->isFinal());
        self::assertTrue((new ReflectionClass($payload))->isReadOnly());
        self::assertSame(['canonicalEvent' => (new PropertyLifecycleEventSerializer)->serialize($this->event())], $payload->fields());
    }

    public function test_round_trip_preserves_canonical_event_byte_for_byte(): void
    {
        $payload = new PropertyLifecycleDeliveryPayload($this->event());
        $restored = PropertyLifecycleDeliveryPayload::restore($payload->fields());

        self::assertEquals($payload->event, $restored->event);
        self::assertSame($payload->fields(), $restored->fields());
        self::assertSame($payload->checksum(), $restored->checksum());
        self::assertSame($payload->event->eventId->value, $restored->event->eventId->value);
        self::assertSame($payload->event->metadata->fields(), $restored->event->metadata->fields());
    }

    public function test_checksum_is_sha256_of_canonical_event_only_and_is_stable(): void
    {
        $first = new PropertyLifecycleDeliveryPayload($this->event());
        $second = new PropertyLifecycleDeliveryPayload($this->event());

        self::assertSame(hash('sha256', $first->fields()['canonicalEvent']), $first->checksum());
        self::assertSame($first->checksum(), $second->checksum());
    }

    public function test_business_event_and_delivery_message_identities_remain_distinct(): void
    {
        $event = $this->event();
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents(
            PublicProjectionDeliverySourceModule::fromString('RealEstateCatalog'),
            PublicProjectionDeliveryAggregateType::fromString('Property'),
            PublicProjectionDeliveryAggregateId::fromString($event->payload->propertyId->value),
            $event->payload->lifecycleVersion,
            PublicProjectionDeliveryEventIndex::fromInt(1),
            PublicProjectionDeliveryEventType::fromString($event->type->value),
            PublicProjectionDeliveryPayloadVersion::fromInt($event->payloadVersion->value),
        );
        $messageId = PublicProjectionDeliveryMessageId::fromIdempotencyKey($key);

        self::assertStringStartsWith('property-lifecycle-', $event->eventId->value);
        self::assertStringStartsWith('ppd-message:', $messageId->value);
        self::assertNotSame($event->eventId->value, $messageId->value);
        self::assertSame($event->eventId->value, PropertyLifecycleDeliveryPayload::restore((new PropertyLifecycleDeliveryPayload($event))->fields())->event->eventId->value);
    }

    #[DataProvider('invalidEnvelopes')]
    public function test_invalid_or_non_canonical_envelope_is_rejected(array $fields): void
    {
        $this->expectException(PropertyLifecycleEventTransportException::class);
        PropertyLifecycleDeliveryPayload::restore($fields);
    }

    /** @return iterable<string, array{array<mixed>}> */
    public static function invalidEnvelopes(): iterable
    {
        yield 'missing canonical event' => [[]];
        yield 'additional envelope field' => [['canonicalEvent' => '{}', 'other' => 'x']];
        yield 'non string canonical event' => [['canonicalEvent' => []]];
        yield 'invalid json' => [['canonicalEvent' => '{']];
        yield 'invalid root shape' => [['canonicalEvent' => '{}']];
    }

    public function test_tampered_business_identity_is_rejected(): void
    {
        $fields = (new PropertyLifecycleDeliveryPayload($this->event()))->fields();
        $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
        $data['eventId'] = 'property-lifecycle-'.str_repeat('0', 64);
        $fields['canonicalEvent'] = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->expectException(PropertyLifecycleEventTransportException::class);
        PropertyLifecycleDeliveryPayload::restore($fields);
    }

    public function test_valid_data_with_non_canonical_key_order_is_rejected(): void
    {
        $fields = (new PropertyLifecycleDeliveryPayload($this->event()))->fields();
        $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
        $reordered = ['eventType' => $data['eventType'], 'eventId' => $data['eventId']] + array_slice($data, 2, null, true);

        $this->expectException(PropertyLifecycleEventTransportException::class);
        PropertyLifecycleDeliveryPayload::restore(['canonicalEvent' => json_encode($reordered, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)]);
    }

    public function test_router_port_receives_only_the_restored_business_event(): void
    {
        $method = new ReflectionMethod(PropertyLifecycleEventRouter::class, 'route');

        self::assertSame(PropertyLifecycleEvent::class, $method->getParameters()[0]->getType()?->getName());
        self::assertSame(PropertyLifecycleEventRoutingResult::class, $method->getReturnType()?->getName());
    }

    #[DataProvider('routingOutcomes')]
    public function test_only_routed_acknowledges_future_delivery(PropertyLifecycleEventRoutingResult $result, PropertyLifecycleEventRoutingStatus $status, bool $acknowledged): void
    {
        self::assertSame($status, $result->status);
        self::assertSame($acknowledged, $result->acknowledgesDelivery());
    }

    /** @return iterable<string, array{PropertyLifecycleEventRoutingResult,PropertyLifecycleEventRoutingStatus,bool}> */
    public static function routingOutcomes(): iterable
    {
        yield 'routed' => [PropertyLifecycleEventRoutingResult::routed(), PropertyLifecycleEventRoutingStatus::Routed, true];
        yield 'deferred' => [PropertyLifecycleEventRoutingResult::deferred(PropertyLifecycleEventRoutingDiagnosticCode::RouteUnavailable), PropertyLifecycleEventRoutingStatus::Deferred, false];
        yield 'retryable failure' => [PropertyLifecycleEventRoutingResult::retryableFailure(PropertyLifecycleEventRoutingDiagnosticCode::TransferFailed), PropertyLifecycleEventRoutingStatus::RetryableFailure, false];
        yield 'rejected' => [PropertyLifecycleEventRoutingResult::rejected(PropertyLifecycleEventRoutingDiagnosticCode::UnsupportedEvent), PropertyLifecycleEventRoutingStatus::Rejected, false];
    }

    public function test_routing_status_and_diagnostics_are_closed(): void
    {
        self::assertSame(['routed', 'deferred', 'retryable_failure', 'rejected'], array_column(PropertyLifecycleEventRoutingStatus::cases(), 'value'));
        self::assertSame(['route_unavailable', 'transfer_failed', 'unsupported_event', 'corrupted_event'], array_column(PropertyLifecycleEventRoutingDiagnosticCode::cases(), 'value'));
    }

    private function event(): PropertyLifecycleEvent
    {
        return (new PropertyLifecycleEventCatalog)->eventsFor(
            PropertyId::fromString('22222222-2222-4222-8222-222222222222'),
            new PropertyLifecycleTransition(PropertyLifecycleState::Draft, PropertyLifecycleState::Active, PropertyLifecycleAction::Activate),
            2,
            new PropertyLifecycleEventMetadata(
                PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:00.000000Z'),
                PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:01.000000Z'),
            ),
        )[0];
    }
}
