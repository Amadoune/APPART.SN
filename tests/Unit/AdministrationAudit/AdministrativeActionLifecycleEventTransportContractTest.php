<?php

namespace Tests\Unit\AdministrationAudit;

use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleDeliveryPayload;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRouter;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingDiagnostic;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingResult;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingStatus;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventTransportException;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportEnvelope;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportSerializer;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEvent;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventCatalog;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventId;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventMetadata;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayloadVersion;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class AdministrativeActionLifecycleEventTransportContractTest extends TestCase
{
    #[DataProvider('transitions')]
    public function test_all_certified_events_round_trip_byte_for_byte(AdministrativeActionLifecycleTransition $transition): void
    {
        $event = $this->event($transition);
        $payload = new AdministrativeActionLifecycleDeliveryPayload($event);
        $restored = AdministrativeActionLifecycleDeliveryPayload::restore($payload->fields());

        self::assertInstanceOf(PublicProjectionDeliveryPayload::class, $payload);
        self::assertEquals($event, $restored->event);
        self::assertSame($payload->fields(), $restored->fields());
        self::assertSame(hash('sha256', $payload->fields()['canonicalEvent']), $payload->checksum());
    }

    public function test_envelope_separates_business_and_transport_identities(): void
    {
        $payload = new AdministrativeActionLifecycleDeliveryPayload($this->event(self::transitions()[0][0]));
        $envelope = AdministrativeActionLifecycleTransportEnvelope::wrap($payload);

        self::assertMatchesRegularExpression('/^administrative-action-lifecycle-delivery-[0-9a-f]{64}$/', $envelope->messageId);
        self::assertSame($payload->event->payload->eventId->value, $envelope->metadata->businessEventId);
        self::assertNotSame($envelope->messageId, $envelope->metadata->businessEventId);
        self::assertSame($payload->checksum(), $envelope->metadata->payloadChecksum);
        self::assertSame(1, $envelope->transportVersion);
    }

    public function test_transport_serialization_is_canonical_and_stable(): void
    {
        $envelope = AdministrativeActionLifecycleTransportEnvelope::wrap(
            new AdministrativeActionLifecycleDeliveryPayload($this->event(self::transitions()[0][0])),
        );
        $serializer = new AdministrativeActionLifecycleTransportSerializer;
        $json = $serializer->serialize($envelope);
        $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame(['messageId', 'messageType', 'transportVersion', 'payload', 'metadata'], array_keys($decoded));
        self::assertSame(['canonicalEvent'], array_keys($decoded['payload']));
        self::assertSame(['source', 'businessEventId', 'payloadChecksum'], array_keys($decoded['metadata']));
        self::assertSame($json, $serializer->serialize($envelope));
    }

    public function test_tampered_event_is_rejected(): void
    {
        $fields = (new AdministrativeActionLifecycleDeliveryPayload($this->event(self::transitions()[0][0])))->fields();
        $data = json_decode($fields['canonicalEvent'], true, flags: JSON_THROW_ON_ERROR);
        $data['payload']['eventId'] = str_repeat('0', 64);
        $fields['canonicalEvent'] = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->expectException(AdministrativeActionLifecycleEventTransportException::class);
        AdministrativeActionLifecycleDeliveryPayload::restore($fields);
    }

    /** @param array<mixed> $fields */
    #[DataProvider('invalidPayloads')]
    public function test_invalid_payload_shape_is_rejected(array $fields): void
    {
        $this->expectException(AdministrativeActionLifecycleEventTransportException::class);
        AdministrativeActionLifecycleDeliveryPayload::restore($fields);
    }

    public function test_router_port_and_results_are_closed(): void
    {
        $method = new ReflectionMethod(AdministrativeActionLifecycleEventRouter::class, 'route');

        self::assertSame(AdministrativeActionLifecycleTransportEnvelope::class, (string) $method->getParameters()[0]->getType());
        self::assertSame(AdministrativeActionLifecycleEventRoutingResult::class, (string) $method->getReturnType());
        self::assertSame(['routed', 'deferred', 'retryable_failure', 'rejected'], array_column(AdministrativeActionLifecycleEventRoutingStatus::cases(), 'value'));
        self::assertTrue(AdministrativeActionLifecycleEventRoutingResult::routed()->acknowledgesDelivery());
        self::assertFalse(AdministrativeActionLifecycleEventRoutingResult::deferred()->acknowledgesDelivery());
        self::assertFalse(AdministrativeActionLifecycleEventRoutingResult::retryableFailure()->acknowledgesDelivery());
        self::assertFalse(AdministrativeActionLifecycleEventRoutingResult::rejected(AdministrativeActionLifecycleEventRoutingDiagnostic::CorruptedEvent)->acknowledgesDelivery());
    }

    public function test_transport_models_are_immutable(): void
    {
        foreach ([
            AdministrativeActionLifecycleDeliveryPayload::class,
            AdministrativeActionLifecycleTransportEnvelope::class,
            AdministrativeActionLifecycleTransportSerializer::class,
            AdministrativeActionLifecycleEventRoutingResult::class,
        ] as $class) {
            $reflection = new ReflectionClass($class);
            self::assertTrue($reflection->isFinal());
            self::assertTrue($reflection->isReadOnly());
        }
    }

    /** @return list<array{AdministrativeActionLifecycleTransition}> */
    public static function transitions(): array
    {
        return [
            [new AdministrativeActionLifecycleTransition(AdministrativeActionLifecycleState::Draft, AdministrativeActionLifecycleState::Recorded, AdministrativeActionLifecycleAction::Record)],
            [new AdministrativeActionLifecycleTransition(AdministrativeActionLifecycleState::Draft, AdministrativeActionLifecycleState::PendingApproval, AdministrativeActionLifecycleAction::Record)],
            [new AdministrativeActionLifecycleTransition(AdministrativeActionLifecycleState::PendingApproval, AdministrativeActionLifecycleState::Approved, AdministrativeActionLifecycleAction::Approve)],
            [new AdministrativeActionLifecycleTransition(AdministrativeActionLifecycleState::PendingApproval, AdministrativeActionLifecycleState::Rejected, AdministrativeActionLifecycleAction::Reject)],
        ];
    }

    /** @return iterable<string, array{array<mixed>}> */
    public static function invalidPayloads(): iterable
    {
        yield 'missing canonical event' => [[]];
        yield 'extra field' => [['canonicalEvent' => '{}', 'extra' => true]];
        yield 'non string event' => [['canonicalEvent' => []]];
        yield 'invalid JSON' => [['canonicalEvent' => '{']];
        yield 'invalid shape' => [['canonicalEvent' => '{}']];
    }

    private function event(AdministrativeActionLifecycleTransition $transition): AdministrativeActionLifecycleEvent
    {
        $actionId = AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000047');
        $type = (new AdministrativeActionLifecycleEventCatalog)->typeFor($transition);
        $version = AdministrativeActionLifecycleEventPayloadVersion::V1;
        $eventId = AdministrativeActionLifecycleEventId::derive($type, $version, $actionId, $transition, 2);
        $occurredAt = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T12:00:00.000000+00:00'));
        $recordedAt = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T12:00:01.000000+00:00'));

        return new AdministrativeActionLifecycleEvent(
            new AdministrativeActionLifecycleEventMetadata($type, $version, ActorId::fromString('decision-actor-001'), $occurredAt, $recordedAt),
            new AdministrativeActionLifecycleEventPayload(
                $eventId,
                $actionId,
                implode('>', [$transition->from->value, $transition->action->value, $transition->to->value]),
                $transition->from,
                $transition->to,
                $transition->action,
                1,
                2,
            ),
        );
    }
}
