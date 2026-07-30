<?php

namespace Tests\Unit\ReservationLifecycleEventTransport;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryMetadata;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryPayload;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleEventRouterPort;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingResult;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingStatus;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportEnvelope;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportSerializer;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEvent;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventCatalog;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventSerializer;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use UnexpectedValueException;

final class ReservationLifecycleEventTransportContractTest extends TestCase
{
    /** @return iterable<string, array{ReservationLifecycleState,ReservationLifecycleAction,ReservationLifecycleState}> */
    public static function certifiedTransitions(): iterable
    {
        foreach ([
            'draft>submit>requested',
            'draft>cancel>cancelled',
            'requested>confirm>confirmed',
            'requested>reject>rejected',
            'requested>cancel>cancelled',
            'requested>expire>expired',
            'confirmed>start>in_progress',
            'confirmed>cancel>cancelled',
            'confirmed>expire>expired',
            'in_progress>complete>completed',
            'in_progress>cancel>cancelled',
        ] as $transition) {
            [$from, $action, $to] = explode('>', $transition);
            yield $transition => [ReservationLifecycleState::from($from), ReservationLifecycleAction::from($action), ReservationLifecycleState::from($to)];
        }
    }

    public function test_delivery_payload_is_the_unique_immutable_adapter_of_the_existing_contract(): void
    {
        $payload = new ReservationLifecycleDeliveryPayload($this->event());

        self::assertInstanceOf(PublicProjectionDeliveryPayload::class, $payload);
        self::assertTrue((new ReflectionClass($payload))->isFinal());
        self::assertTrue((new ReflectionClass($payload))->isReadOnly());
        self::assertSame(['canonicalEvent' => (new ReservationLifecycleEventSerializer)->serialize($this->event())], $payload->fields());
    }

    #[DataProvider('certifiedTransitions')]
    public function test_all_eleven_certified_events_are_transport_compatible(ReservationLifecycleState $from, ReservationLifecycleAction $action, ReservationLifecycleState $to): void
    {
        $event = (new ReservationLifecycleEventCatalog)->eventFor($this->reservationId(), new ReservationLifecycleTransition($from, $to, $action), 2);
        $payload = new ReservationLifecycleDeliveryPayload($event);
        $restored = ReservationLifecycleDeliveryPayload::restore($payload->fields());

        self::assertEquals($event, $restored->event);
        self::assertSame($payload->fields(), $restored->fields());
        self::assertSame($event->payload->eventId->value, $restored->event->payload->eventId->value);
    }

    public function test_round_trip_preserves_the_canonical_business_event_byte_for_byte(): void
    {
        $payload = new ReservationLifecycleDeliveryPayload($this->event());
        $restored = ReservationLifecycleDeliveryPayload::restore($payload->fields());

        self::assertSame($payload->fields()['canonicalEvent'], $restored->fields()['canonicalEvent']);
        self::assertSame($payload->checksum(), $restored->checksum());
        self::assertSame(hash('sha256', $payload->fields()['canonicalEvent']), $payload->checksum());
    }

    public function test_envelope_adds_only_stable_technical_identity_type_version_payload_and_metadata(): void
    {
        $payload = new ReservationLifecycleDeliveryPayload($this->event());
        $first = ReservationLifecycleTransportEnvelope::wrap($payload);
        $second = ReservationLifecycleTransportEnvelope::wrap(new ReservationLifecycleDeliveryPayload($this->event()));

        self::assertEquals($first, $second);
        self::assertMatchesRegularExpression('/^reservation-lifecycle-delivery-[0-9a-f]{64}$/', $first->messageId);
        self::assertSame('reservation.lifecycle.submitted', $first->messageType);
        self::assertSame(1, $first->transportVersion);
        self::assertSame($payload->event->payload->eventId->value, $first->metadata->businessEventId);
        self::assertSame($payload->checksum(), $first->metadata->payloadChecksum);
        self::assertNotSame($first->messageId, $first->metadata->businessEventId);
    }

    public function test_transport_serialization_has_fixed_root_payload_and_metadata_order(): void
    {
        $envelope = ReservationLifecycleTransportEnvelope::wrap(new ReservationLifecycleDeliveryPayload($this->event()));
        $serializer = new ReservationLifecycleTransportSerializer;
        $json = $serializer->serialize($envelope);
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame(['messageId', 'messageType', 'transportVersion', 'payload', 'metadata'], array_keys($decoded));
        self::assertSame(['canonicalEvent'], array_keys($decoded['payload']));
        self::assertSame(['source', 'businessEventId', 'payloadChecksum'], array_keys($decoded['metadata']));
        self::assertSame((new ReservationLifecycleEventSerializer)->serialize($this->event()), $decoded['payload']['canonicalEvent']);
        self::assertSame($json, $serializer->serialize($envelope));
    }

    public function test_transport_never_recalculates_or_replaces_the_business_identity(): void
    {
        $event = $this->event();
        $businessId = $event->payload->eventId->value;
        $envelope = ReservationLifecycleTransportEnvelope::wrap(new ReservationLifecycleDeliveryPayload($event));
        $restored = ReservationLifecycleDeliveryPayload::restore($envelope->payload->fields());

        self::assertSame($businessId, $event->payload->eventId->value);
        self::assertSame($businessId, $envelope->metadata->businessEventId);
        self::assertSame($businessId, $restored->event->payload->eventId->value);
        self::assertNotSame($businessId, $envelope->messageId);
    }

    /** @param array<mixed> $fields */
    #[DataProvider('invalidPayloadEnvelopes')]
    public function test_invalid_payload_envelope_is_rejected(array $fields): void
    {
        $this->expectException(UnexpectedValueException::class);
        ReservationLifecycleDeliveryPayload::restore($fields);
    }

    /** @return iterable<string, array{array<mixed>}> */
    public static function invalidPayloadEnvelopes(): iterable
    {
        yield 'missing event' => [[]];
        yield 'additional field' => [['canonicalEvent' => '{}', 'extra' => true]];
        yield 'non string event' => [['canonicalEvent' => []]];
        yield 'invalid json' => [['canonicalEvent' => '{']];
        yield 'invalid shape' => [['canonicalEvent' => '{}']];
    }

    public function test_tampered_business_identity_is_rejected(): void
    {
        $fields = (new ReservationLifecycleDeliveryPayload($this->event()))->fields();
        $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
        $data['eventId'] = 'reservation-lifecycle-'.str_repeat('0', 64);
        $fields['canonicalEvent'] = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->expectException(UnexpectedValueException::class);
        ReservationLifecycleDeliveryPayload::restore($fields);
    }

    public function test_non_canonical_business_field_order_is_rejected(): void
    {
        $canonicalEvent = (new ReservationLifecycleDeliveryPayload($this->event()))->fields()['canonicalEvent'];
        $data = json_decode($canonicalEvent, true, flags: JSON_THROW_ON_ERROR);
        $reordered = ['aggregateType' => $data['aggregateType'], 'eventId' => $data['eventId']] + array_slice($data, 2, null, true);

        $this->expectException(UnexpectedValueException::class);
        ReservationLifecycleDeliveryPayload::restore([
            'canonicalEvent' => json_encode($reordered, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function test_metadata_envelope_and_serializer_are_immutable(): void
    {
        foreach ([ReservationLifecycleDeliveryMetadata::class, ReservationLifecycleTransportEnvelope::class, ReservationLifecycleTransportSerializer::class] as $class) {
            $reflection = new ReflectionClass($class);
            self::assertTrue($reflection->isFinal(), $class);
            self::assertTrue($reflection->isReadOnly(), $class);
        }
    }

    public function test_router_is_a_pure_port_with_a_closed_result_for_the_transport_envelope(): void
    {
        $contract = new ReflectionClass(ReservationLifecycleEventRouterPort::class);
        $method = new ReflectionMethod(ReservationLifecycleEventRouterPort::class, 'route');

        self::assertTrue($contract->isInterface());
        self::assertSame(ReservationLifecycleTransportEnvelope::class, $method->getParameters()[0]->getType()?->getName());
        self::assertSame(ReservationLifecycleRoutingResult::class, $method->getReturnType()?->getName());
    }

    public function test_routing_result_is_closed_immutable_and_covers_the_four_certified_statuses(): void
    {
        self::assertSame(
            ['stored', 'already_stored', 'corrupted_envelope', 'persistence_corrupted'],
            array_column(ReservationLifecycleRoutingStatus::cases(), 'value'),
        );

        $reflection = new ReflectionClass(ReservationLifecycleRoutingResult::class);
        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
        foreach (ReservationLifecycleRoutingStatus::cases() as $status) {
            self::assertSame($status, (new ReservationLifecycleRoutingResult($status))->status);
        }
    }

    private function event(): ReservationLifecycleEvent
    {
        return (new ReservationLifecycleEventCatalog)->eventFor(
            $this->reservationId(),
            new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit),
            2,
        );
    }

    private function reservationId(): ReservationId
    {
        return ReservationId::fromString('22222222-2222-4222-8222-222222222222');
    }
}
