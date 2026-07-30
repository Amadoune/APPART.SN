<?php

namespace Tests\Unit\ProfessionalStatusEventTransport;

use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusDeliveryPayload;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRouter;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingDiagnostic;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingResult;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingStatus;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventTransportException;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportEnvelope;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportSerializer;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEvent;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventCatalog;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventId;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventMetadata;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayload;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayloadVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class ProfessionalStatusEventTransportContractTest extends TestCase
{
    #[DataProvider('transitions')]
    public function test_all_certified_events_round_trip_byte_for_byte(ProfessionalStatusTransition $transition): void
    {
        $event = $this->event($transition);
        $payload = new ProfessionalStatusDeliveryPayload($event);
        $restored = ProfessionalStatusDeliveryPayload::restore($payload->fields());

        self::assertInstanceOf(PublicProjectionDeliveryPayload::class, $payload);
        self::assertEquals($event, $restored->event);
        self::assertSame($payload->fields(), $restored->fields());
        self::assertSame(hash('sha256', $payload->fields()['canonicalEvent']), $payload->checksum());
    }

    public function test_envelope_separates_business_and_transport_identities(): void
    {
        $payload = new ProfessionalStatusDeliveryPayload($this->event(self::transitions()[0][0]));
        $envelope = ProfessionalStatusTransportEnvelope::wrap($payload);

        self::assertMatchesRegularExpression('/^professional-status-delivery-[0-9a-f]{64}$/', $envelope->messageId);
        self::assertSame($payload->event->payload->eventId->value, $envelope->metadata->businessEventId);
        self::assertNotSame($envelope->messageId, $envelope->metadata->businessEventId);
        self::assertSame($payload->checksum(), $envelope->metadata->payloadChecksum);
        self::assertSame(1, $envelope->transportVersion);
    }

    public function test_transport_serialization_is_canonical_and_stable(): void
    {
        $envelope = ProfessionalStatusTransportEnvelope::wrap(new ProfessionalStatusDeliveryPayload($this->event(self::transitions()[0][0])));
        $serializer = new ProfessionalStatusTransportSerializer;
        $json = $serializer->serialize($envelope);
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame(['messageId', 'messageType', 'transportVersion', 'payload', 'metadata'], array_keys($decoded));
        self::assertSame(['canonicalEvent'], array_keys($decoded['payload']));
        self::assertSame(['source', 'businessEventId', 'payloadChecksum'], array_keys($decoded['metadata']));
        self::assertSame($json, $serializer->serialize($envelope));
    }

    public function test_tampered_event_is_rejected(): void
    {
        $fields = (new ProfessionalStatusDeliveryPayload($this->event(self::transitions()[0][0])))->fields();
        $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
        $data['payload']['eventId'] = str_repeat('0', 64);
        $fields['canonicalEvent'] = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->expectException(ProfessionalStatusEventTransportException::class);
        ProfessionalStatusDeliveryPayload::restore($fields);
    }

    /** @param array<mixed> $fields */
    #[DataProvider('invalidEnvelopes')]
    public function test_invalid_payload_shape_is_rejected(array $fields): void
    {
        $this->expectException(ProfessionalStatusEventTransportException::class);
        ProfessionalStatusDeliveryPayload::restore($fields);
    }

    public function test_router_port_and_results_are_closed(): void
    {
        $method = new ReflectionMethod(ProfessionalStatusEventRouter::class, 'route');
        self::assertSame(ProfessionalStatusTransportEnvelope::class, $method->getParameters()[0]->getType()?->getName());
        self::assertSame(ProfessionalStatusEventRoutingResult::class, $method->getReturnType()?->getName());
        self::assertSame(['routed', 'deferred', 'retryable_failure', 'rejected'], array_column(ProfessionalStatusEventRoutingStatus::cases(), 'value'));
        self::assertTrue(ProfessionalStatusEventRoutingResult::routed()->acknowledgesDelivery());
        self::assertFalse(ProfessionalStatusEventRoutingResult::deferred()->acknowledgesDelivery());
        self::assertFalse(ProfessionalStatusEventRoutingResult::retryableFailure()->acknowledgesDelivery());
        self::assertFalse(ProfessionalStatusEventRoutingResult::rejected(ProfessionalStatusEventRoutingDiagnostic::CorruptedEvent)->acknowledgesDelivery());
    }

    public function test_transport_models_are_immutable(): void
    {
        foreach ([ProfessionalStatusDeliveryPayload::class, ProfessionalStatusTransportEnvelope::class, ProfessionalStatusTransportSerializer::class, ProfessionalStatusEventRoutingResult::class] as $class) {
            $reflection = new ReflectionClass($class);
            self::assertTrue($reflection->isFinal());
            self::assertTrue($reflection->isReadOnly());
        }
    }

    /** @return list<array{ProfessionalStatusTransition}> */
    public static function transitions(): array
    {
        return [
            [new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend)],
            [new ProfessionalStatusTransition(ProfessionalStatusState::Suspended, ProfessionalStatusState::Active, ProfessionalStatusAction::Reactivate)],
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

    private function event(ProfessionalStatusTransition $transition): ProfessionalStatusEvent
    {
        $professionalId = ProfessionalStatusId::fromString('a4100000-0000-4000-8000-000000000099');
        $type = (new ProfessionalStatusEventCatalog)->typeFor($transition);
        $version = ProfessionalStatusEventPayloadVersion::V1;
        $eventId = ProfessionalStatusEventId::derive($type, $version, $professionalId, $transition, 2);
        $at = ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));

        return new ProfessionalStatusEvent(
            new ProfessionalStatusEventMetadata($type, $version, ProfessionalStatusActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at),
            new ProfessionalStatusEventPayload($eventId, $professionalId, implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]), $transition->from, $transition->to, $transition->action, 1, 2),
        );
    }
}
