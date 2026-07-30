<?php

namespace Tests\Unit\LeadLifecycleEventTransport;

use App\Application\LeadLifecycleEventTransport\LeadLifecycleDeliveryPayload;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRouter;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingDiagnostic;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingResult;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingStatus;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventTransportException;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportEnvelope;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportSerializer;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEvent;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventCatalog;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventMetadata;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayload;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayloadVersion;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class LeadLifecycleEventTransportContractTest extends TestCase
{
    #[DataProvider('transitions')]
    public function test_all_certified_events_round_trip_byte_for_byte(LeadLifecycleTransition $transition): void
    {
        $event = $this->event($transition);
        $payload = new LeadLifecycleDeliveryPayload($event);
        $restored = LeadLifecycleDeliveryPayload::restore($payload->fields());

        self::assertInstanceOf(PublicProjectionDeliveryPayload::class, $payload);
        self::assertEquals($event, $restored->event);
        self::assertSame($payload->fields(), $restored->fields());
        self::assertSame(hash('sha256', $payload->fields()['canonicalEvent']), $payload->checksum());
    }

    public function test_delivery_envelope_separates_business_and_transport_identities(): void
    {
        $payload = new LeadLifecycleDeliveryPayload($this->event(self::transitions()[0][0]));
        $envelope = LeadLifecycleTransportEnvelope::wrap($payload);

        self::assertMatchesRegularExpression('/^lead-lifecycle-delivery-[0-9a-f]{64}$/', $envelope->messageId);
        self::assertSame($payload->event->payload->eventId->value, $envelope->metadata->businessEventId);
        self::assertNotSame($envelope->messageId, $envelope->metadata->businessEventId);
        self::assertSame($payload->checksum(), $envelope->metadata->payloadChecksum);
        self::assertSame(1, $envelope->transportVersion);
    }

    public function test_transport_serialization_is_canonical_and_stable(): void
    {
        $envelope = LeadLifecycleTransportEnvelope::wrap(new LeadLifecycleDeliveryPayload($this->event(self::transitions()[0][0])));
        $serializer = new LeadLifecycleTransportSerializer;
        $json = $serializer->serialize($envelope);
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame(['messageId', 'messageType', 'transportVersion', 'payload', 'metadata'], array_keys($decoded));
        self::assertSame(['canonicalEvent'], array_keys($decoded['payload']));
        self::assertSame(['source', 'businessEventId', 'payloadChecksum'], array_keys($decoded['metadata']));
        self::assertSame($json, $serializer->serialize($envelope));
    }

    public function test_tampered_or_non_canonical_event_is_rejected(): void
    {
        $fields = (new LeadLifecycleDeliveryPayload($this->event(self::transitions()[0][0])))->fields();
        $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
        $data['payload']['eventId'] = str_repeat('0', 64);
        $fields['canonicalEvent'] = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->expectException(LeadLifecycleEventTransportException::class);
        LeadLifecycleDeliveryPayload::restore($fields);
    }

    /** @param array<mixed> $fields */
    #[DataProvider('invalidEnvelopes')]
    public function test_invalid_delivery_payload_shape_is_rejected(array $fields): void
    {
        $this->expectException(LeadLifecycleEventTransportException::class);
        LeadLifecycleDeliveryPayload::restore($fields);
    }

    public function test_router_port_and_results_are_closed(): void
    {
        $method = new ReflectionMethod(LeadLifecycleEventRouter::class, 'route');
        self::assertSame(LeadLifecycleTransportEnvelope::class, $method->getParameters()[0]->getType()?->getName());
        self::assertSame(LeadLifecycleEventRoutingResult::class, $method->getReturnType()?->getName());
        self::assertSame(['routed', 'deferred', 'retryable_failure', 'rejected'], array_column(LeadLifecycleEventRoutingStatus::cases(), 'value'));
        self::assertTrue(LeadLifecycleEventRoutingResult::routed()->acknowledgesDelivery());
        self::assertFalse(LeadLifecycleEventRoutingResult::deferred()->acknowledgesDelivery());
        self::assertFalse(LeadLifecycleEventRoutingResult::retryableFailure()->acknowledgesDelivery());
        self::assertFalse(LeadLifecycleEventRoutingResult::rejected(LeadLifecycleEventRoutingDiagnostic::CorruptedEvent)->acknowledgesDelivery());
    }

    public function test_transport_models_are_immutable(): void
    {
        foreach ([LeadLifecycleDeliveryPayload::class, LeadLifecycleTransportEnvelope::class, LeadLifecycleTransportSerializer::class, LeadLifecycleEventRoutingResult::class] as $class) {
            $reflection = new ReflectionClass($class);
            self::assertTrue($reflection->isFinal());
            self::assertTrue($reflection->isReadOnly());
        }
    }

    /** @return list<array{LeadLifecycleTransition}> */
    public static function transitions(): array
    {
        return [
            [new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver)],
            [new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Rejected, LeadLifecycleAction::Reject)],
            [new LeadLifecycleTransition(LeadLifecycleState::Delivered, LeadLifecycleState::Closed, LeadLifecycleAction::Close)],
            [new LeadLifecycleTransition(LeadLifecycleState::Rejected, LeadLifecycleState::Closed, LeadLifecycleAction::Close)],
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

    private function event(LeadLifecycleTransition $transition): LeadLifecycleEvent
    {
        $leadId = LeadId::fromString('a4100000-0000-4000-8000-000000000098');
        $type = (new LeadLifecycleEventCatalog)->typeFor($transition);
        $payloadVersion = LeadLifecycleEventPayloadVersion::V1;
        $eventId = LeadLifecycleEventId::derive($type, $payloadVersion, $leadId, $transition, 2);
        $at = LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00'));

        return new LeadLifecycleEvent(
            new LeadLifecycleEventMetadata($type, $payloadVersion, LeadLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at),
            new LeadLifecycleEventPayload($eventId, $leadId, implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]), $transition->from, $transition->to, $transition->action, 1, 2),
        );
    }
}
