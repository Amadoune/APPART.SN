<?php

namespace Tests\Unit\Modules\Geography;

use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleDeliveryPayload;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRouter;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingDiagnostic;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingResult;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingStatus;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventTransportException;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportEnvelope;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportSerializer;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportVersion;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Application\PlaceLifecycleEvent\PlaceLifecycleEventCatalog;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeActorId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeContextV1;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeExpectedSourceVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeIntentId;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedState;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeObservedTargetVersion;
use Appart\Modules\Geography\Application\PlaceMergeContext\PlaceMergeOccurredAt;
use Appart\Modules\Geography\Domain\ValueObject\CountryCode;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

final class PlaceLifecycleEventTransportContractTest extends TestCase
{
    #[DataProvider('transitions')]
    public function test_every_certified_event_round_trips_byte_for_byte(
        PlaceLifecycleTransition $transition,
    ): void {
        $event = (new PlaceLifecycleEventCatalog)->eventFor($transition, self::context(), 8);
        $payload = new PlaceLifecycleDeliveryPayload($event);
        $envelope = PlaceLifecycleTransportEnvelope::wrap($payload);
        $serializer = new PlaceLifecycleTransportSerializer;
        $bytes = $serializer->serialize($envelope);
        $restored = $serializer->restore($bytes);

        self::assertSame($bytes, $serializer->serialize($restored));
        self::assertSame($payload->fields(), $restored->payload->fields());
        self::assertSame(
            hash('sha256', $payload->fields()['canonicalEvent']),
            $payload->transportChecksum()->value,
        );
        self::assertSame($payload->transportChecksum()->value, $payload->checksum());
    }

    public function test_business_and_transport_identities_are_strictly_separated(): void
    {
        $envelope = self::envelope(self::transitions()[0][0]);

        self::assertMatchesRegularExpression(
            '/^place-lifecycle-delivery-[a-f0-9]{64}$/',
            $envelope->messageId->value,
        );
        self::assertSame($envelope->payload->event->eventId->value, $envelope->metadata->eventId);
        self::assertNotSame($envelope->messageId->value, $envelope->metadata->eventId);
        self::assertSame(PlaceLifecycleTransportVersion::V1, $envelope->transportVersion);
    }

    public function test_serialization_shape_is_canonical_and_stable(): void
    {
        $serializer = new PlaceLifecycleTransportSerializer;
        $envelope = self::envelope(self::transitions()[2][0]);
        $bytes = $serializer->serialize($envelope);
        $decoded = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame(
            ['messageId', 'messageType', 'transportVersion', 'payload', 'metadata'],
            array_keys($decoded),
        );
        self::assertSame(['canonicalEvent'], array_keys($decoded['payload']));
        self::assertSame(['source', 'eventId', 'payloadChecksum'], array_keys($decoded['metadata']));
        self::assertSame($bytes, $serializer->serialize($envelope));
    }

    public function test_payload_remains_an_opaque_canonical_event(): void
    {
        $payload = self::envelope(self::transitions()[2][0])->payload;

        self::assertSame(['canonicalEvent'], array_keys($payload->fields()));
        self::assertStringContainsString('"targetPlaceId"', $payload->fields()['canonicalEvent']);
    }

    public function test_tampered_event_is_rejected(): void
    {
        $fields = self::envelope(self::transitions()[2][0])->payload->fields();
        $event = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
        $event['payload']['targetPlaceId'] = '10000000-0000-4000-8000-000000000099';
        $fields['canonicalEvent'] = json_encode(
            $event,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );

        $this->expectException(PlaceLifecycleEventTransportException::class);
        PlaceLifecycleDeliveryPayload::restore($fields);
    }

    public function test_non_canonical_envelope_is_rejected_byte_for_byte(): void
    {
        $serializer = new PlaceLifecycleTransportSerializer;
        $bytes = $serializer->serialize(self::envelope(self::transitions()[0][0]));
        $nonCanonical = str_replace('{"messageId"', '{ "messageId"', $bytes);

        $this->expectException(PlaceLifecycleEventTransportException::class);
        $serializer->restore($nonCanonical);
    }

    /** @param array<mixed> $fields */
    #[DataProvider('invalidPayloads')]
    public function test_invalid_opaque_payload_shapes_are_rejected(array $fields): void
    {
        $this->expectException(PlaceLifecycleEventTransportException::class);
        PlaceLifecycleDeliveryPayload::restore($fields);
    }

    public function test_router_port_and_routing_results_are_closed(): void
    {
        $method = new ReflectionMethod(PlaceLifecycleEventRouter::class, 'route');
        $parameterType = $method->getParameters()[0]->getType();
        $returnType = $method->getReturnType();

        self::assertInstanceOf(ReflectionNamedType::class, $parameterType);
        self::assertInstanceOf(ReflectionNamedType::class, $returnType);
        self::assertSame(PlaceLifecycleTransportEnvelope::class, $parameterType->getName());
        self::assertSame(PlaceLifecycleEventRoutingResult::class, $returnType->getName());
        self::assertSame(
            ['routed', 'deferred', 'retryable_failure', 'rejected'],
            array_column(PlaceLifecycleEventRoutingStatus::cases(), 'value'),
        );
        self::assertTrue(PlaceLifecycleEventRoutingResult::routed()->acknowledgesDelivery());
        self::assertFalse(PlaceLifecycleEventRoutingResult::deferred()->acknowledgesDelivery());
        self::assertFalse(PlaceLifecycleEventRoutingResult::retryableFailure()->acknowledgesDelivery());
        self::assertFalse(
            PlaceLifecycleEventRoutingResult::rejected(
                PlaceLifecycleEventRoutingDiagnostic::CorruptedEvent,
            )->acknowledgesDelivery(),
        );
    }

    public function test_transport_contract_models_are_immutable(): void
    {
        foreach ([
            PlaceLifecycleDeliveryPayload::class,
            PlaceLifecycleTransportEnvelope::class,
            PlaceLifecycleTransportSerializer::class,
            PlaceLifecycleEventRoutingResult::class,
        ] as $class) {
            $reflection = new ReflectionClass($class);
            self::assertTrue($reflection->isFinal());
            self::assertTrue($reflection->isReadOnly());
        }
    }

    /** @return list<array{PlaceLifecycleTransition}> */
    public static function transitions(): array
    {
        return [
            [new PlaceLifecycleTransition(
                PlaceLifecycleState::Disabled,
                PlaceLifecycleAction::Enable,
                PlaceLifecycleState::Enabled,
            )],
            [new PlaceLifecycleTransition(
                PlaceLifecycleState::Enabled,
                PlaceLifecycleAction::Disable,
                PlaceLifecycleState::Disabled,
            )],
            [new PlaceLifecycleTransition(
                PlaceLifecycleState::Enabled,
                PlaceLifecycleAction::Merge,
                PlaceLifecycleState::Merged,
            )],
            [new PlaceLifecycleTransition(
                PlaceLifecycleState::Disabled,
                PlaceLifecycleAction::Merge,
                PlaceLifecycleState::Merged,
            )],
        ];
    }

    /** @return iterable<string, array{array<mixed>}> */
    public static function invalidPayloads(): iterable
    {
        yield 'missing event' => [[]];
        yield 'extra field' => [['canonicalEvent' => '{}', 'extra' => true]];
        yield 'non-string event' => [['canonicalEvent' => []]];
        yield 'invalid JSON' => [['canonicalEvent' => '{']];
        yield 'invalid event shape' => [['canonicalEvent' => '{}']];
    }

    private static function envelope(
        PlaceLifecycleTransition $transition,
    ): PlaceLifecycleTransportEnvelope {
        $event = (new PlaceLifecycleEventCatalog)->eventFor($transition, self::context(), 8);

        return PlaceLifecycleTransportEnvelope::wrap(new PlaceLifecycleDeliveryPayload($event));
    }

    private static function context(): PlaceMergeContextV1
    {
        return new PlaceMergeContextV1(
            sourceId: PlaceId::fromString('10000000-0000-4000-8000-000000000001'),
            targetId: PlaceId::fromString('10000000-0000-4000-8000-000000000002'),
            expectedSourceVersion: new PlaceMergeExpectedSourceVersion(7),
            observedTargetVersion: new PlaceMergeObservedTargetVersion(11),
            observedTargetState: PlaceMergeObservedState::Enabled,
            observedSourceType: PlaceType::City,
            observedTargetType: PlaceType::City,
            observedSourceCountry: CountryCode::fromString('SN'),
            observedTargetCountry: CountryCode::fromString('SN'),
            actor: PlaceMergeActorId::fromString('20000000-0000-4000-8000-000000000001'),
            occurredAt: PlaceMergeOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-25T10:11:12.123456+00:00')),
            intentId: PlaceMergeIntentId::fromString('30000000-0000-4000-8000-000000000001'),
        );
    }
}
