<?php

namespace Tests\Unit\MediaItemLifecycleEventTransport;

use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleDeliveryPayload;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRouter;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingDiagnostic;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingResult;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventRoutingStatus;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleEventTransportException;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportEnvelope;
use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportSerializer;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEvent;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventCatalog;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventId;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventMetadata;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayload;
use Appart\Modules\Media\Application\MediaItemLifecycleEvent\MediaItemLifecycleEventPayloadVersion;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class MediaItemLifecycleEventTransportContractTest extends TestCase
{
    #[DataProvider('transitions')]
    public function test_all_certified_events_round_trip_byte_for_byte(MediaItemLifecycleTransition $transition): void
    {
        $event = $this->event($transition);
        $payload = new MediaItemLifecycleDeliveryPayload($event);
        $restored = MediaItemLifecycleDeliveryPayload::restore($payload->fields());

        self::assertInstanceOf(PublicProjectionDeliveryPayload::class, $payload);
        self::assertEquals($event, $restored->event);
        self::assertSame($payload->fields(), $restored->fields());
        self::assertSame(hash('sha256', $payload->fields()['canonicalEvent']), $payload->checksum());
    }

    public function test_envelope_separates_business_and_transport_identities(): void
    {
        $payload = new MediaItemLifecycleDeliveryPayload($this->event(self::transitions()[0][0]));
        $envelope = MediaItemLifecycleTransportEnvelope::wrap($payload);

        self::assertMatchesRegularExpression('/^media-item-lifecycle-delivery-[0-9a-f]{64}$/', $envelope->messageId);
        self::assertSame($payload->event->payload->eventId->value, $envelope->metadata->businessEventId);
        self::assertNotSame($envelope->messageId, $envelope->metadata->businessEventId);
        self::assertSame($payload->checksum(), $envelope->metadata->payloadChecksum);
        self::assertSame(1, $envelope->transportVersion);
    }

    public function test_transport_serialization_is_canonical_and_stable(): void
    {
        $envelope = MediaItemLifecycleTransportEnvelope::wrap(new MediaItemLifecycleDeliveryPayload($this->event(self::transitions()[0][0])));
        $serializer = new MediaItemLifecycleTransportSerializer;
        $json = $serializer->serialize($envelope);
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame(['messageId', 'messageType', 'transportVersion', 'payload', 'metadata'], array_keys($decoded));
        self::assertSame(['canonicalEvent'], array_keys($decoded['payload']));
        self::assertSame(['source', 'businessEventId', 'payloadChecksum'], array_keys($decoded['metadata']));
        self::assertSame($json, $serializer->serialize($envelope));
    }

    public function test_tampered_event_is_rejected(): void
    {
        $fields = (new MediaItemLifecycleDeliveryPayload($this->event(self::transitions()[0][0])))->fields();
        $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
        $data['payload']['eventId'] = str_repeat('0', 64);
        $fields['canonicalEvent'] = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->expectException(MediaItemLifecycleEventTransportException::class);
        MediaItemLifecycleDeliveryPayload::restore($fields);
    }

    /** @param array<mixed> $fields */
    #[DataProvider('invalidEnvelopes')]
    public function test_invalid_payload_shape_is_rejected(array $fields): void
    {
        $this->expectException(MediaItemLifecycleEventTransportException::class);
        MediaItemLifecycleDeliveryPayload::restore($fields);
    }

    public function test_router_port_and_results_are_closed(): void
    {
        $method = new ReflectionMethod(MediaItemLifecycleEventRouter::class, 'route');
        self::assertSame(MediaItemLifecycleTransportEnvelope::class, (string) $method->getParameters()[0]->getType());
        self::assertSame(MediaItemLifecycleEventRoutingResult::class, (string) $method->getReturnType());
        self::assertSame(['routed', 'deferred', 'retryable_failure', 'rejected'], array_column(MediaItemLifecycleEventRoutingStatus::cases(), 'value'));
        self::assertTrue(MediaItemLifecycleEventRoutingResult::routed()->acknowledgesDelivery());
        self::assertFalse(MediaItemLifecycleEventRoutingResult::deferred()->acknowledgesDelivery());
        self::assertFalse(MediaItemLifecycleEventRoutingResult::retryableFailure()->acknowledgesDelivery());
        self::assertFalse(MediaItemLifecycleEventRoutingResult::rejected(MediaItemLifecycleEventRoutingDiagnostic::CorruptedEvent)->acknowledgesDelivery());
    }

    public function test_transport_models_are_immutable(): void
    {
        foreach ([MediaItemLifecycleDeliveryPayload::class, MediaItemLifecycleTransportEnvelope::class, MediaItemLifecycleTransportSerializer::class, MediaItemLifecycleEventRoutingResult::class] as $class) {
            $reflection = new ReflectionClass($class);
            self::assertTrue($reflection->isFinal());
            self::assertTrue($reflection->isReadOnly());
        }
    }

    /** @return list<array{MediaItemLifecycleTransition}> */
    public static function transitions(): array
    {
        return [
            [new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove)],
            [new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Archived, MediaItemLifecycleAction::Archive)],
        ];
    }

    /** @return iterable<string, array{array<mixed>}> */
    public static function invalidEnvelopes(): iterable
    {
        yield 'missing canonical event' => [[]];
        yield 'extra field' => [['canonicalEvent' => '{}', 'extra' => true]];
        yield 'non string event' => [['canonicalEvent' => []]];
        yield 'invalid JSON' => [['canonicalEvent' => '{']];
        yield 'invalid shape' => [['canonicalEvent' => '{}']];
    }

    private function event(MediaItemLifecycleTransition $transition): MediaItemLifecycleEvent
    {
        $mediaId = MediaItemLifecycleId::fromString('a4100000-0000-4000-8000-000000000099');
        $type = (new MediaItemLifecycleEventCatalog)->typeFor($transition);
        $version = MediaItemLifecycleEventPayloadVersion::V1;
        $eventId = MediaItemLifecycleEventId::derive($type, $version, $mediaId, $transition, 2);
        $at = MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));

        return new MediaItemLifecycleEvent(
            new MediaItemLifecycleEventMetadata($type, $version, MediaItemLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at),
            new MediaItemLifecycleEventPayload($eventId, $mediaId, implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]), $transition->from, $transition->to, $transition->action, 1, 2),
        );
    }
}
