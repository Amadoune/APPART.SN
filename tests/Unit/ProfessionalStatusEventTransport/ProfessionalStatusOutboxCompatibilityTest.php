<?php

namespace Tests\Unit\ProfessionalStatusEventTransport;

use App\Application\ProfessionalStatusEventConsumption\ProfessionalStatusDeliveryConsumer;
use App\Application\ProfessionalStatusEventConsumption\ProfessionalStatusDeliveryConsumptionPolicy;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusDeliveryPayload;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRouter;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingDiagnostic;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusEventRoutingResult;
use App\Application\ProfessionalStatusEventTransport\ProfessionalStatusTransportEnvelope;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryIdempotencyKey;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEvent;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventCatalog;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventId;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventMetadata;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayload;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventPayloadVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusEvent\ProfessionalStatusEventType;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProfessionalStatusOutboxCompatibilityTest extends TestCase
{
    public function test_catalog_accepts_exactly_two_professional_status_events(): void
    {
        $catalog = new PublicProjectionDeliveryEventCatalog;
        $payload = new ProfessionalStatusDeliveryPayload($this->event());
        foreach (ProfessionalStatusEventType::cases() as $type) {
            self::assertTrue($catalog->accepts(PublicProjectionDeliveryEventType::fromString($type->value), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('Professionals'), PublicProjectionDeliveryAggregateType::fromString('ProfessionalStatus'), $payload));
        }
        self::assertCount(2, ProfessionalStatusEventType::cases());
    }

    #[DataProvider('outcomes')]
    public function test_consumer_routes_once_and_applies_policy(ProfessionalStatusEventRoutingResult $routing, PublicProjectionDeliveryConsumptionResult $expected): void
    {
        $router = new RecordingProfessionalStatusRouter($routing);
        $message = $this->message();
        $result = (new ProfessionalStatusDeliveryConsumer($router, new ProfessionalStatusDeliveryConsumptionPolicy))->consume($message);
        self::assertSame($expected, $result);
        self::assertCount(1, $router->envelopes);
        self::assertSame($message->payload->fields(), $router->envelopes[0]->payload->fields());
    }

    public function test_divergent_metadata_never_reaches_router(): void
    {
        $router = new RecordingProfessionalStatusRouter(ProfessionalStatusEventRoutingResult::routed());
        $message = $this->message();
        $divergent = new PublicProjectionDeliveryMessage($message->messageId, $message->idempotencyKey, $message->eventType, $message->payloadVersion, $message->sourceModule, PublicProjectionDeliveryAggregateType::fromString('Listing'), $message->aggregateId, $message->order, $message->occurredAt, $message->recordedAt, $message->payload);
        self::assertSame(PublicProjectionDeliveryConsumptionResult::DivergentPayload, (new ProfessionalStatusDeliveryConsumer($router, new ProfessionalStatusDeliveryConsumptionPolicy))->consume($divergent));
        self::assertSame([], $router->envelopes);
    }

    /** @return iterable<string,array{ProfessionalStatusEventRoutingResult,PublicProjectionDeliveryConsumptionResult}> */
    public static function outcomes(): iterable
    {
        yield 'routed' => [ProfessionalStatusEventRoutingResult::routed(), PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'deferred' => [ProfessionalStatusEventRoutingResult::deferred(), PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'retryable' => [ProfessionalStatusEventRoutingResult::retryableFailure(), PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'unsupported' => [ProfessionalStatusEventRoutingResult::rejected(ProfessionalStatusEventRoutingDiagnostic::UnsupportedEvent), PublicProjectionDeliveryConsumptionResult::UnsupportedEventType];
        yield 'corrupted' => [ProfessionalStatusEventRoutingResult::rejected(ProfessionalStatusEventRoutingDiagnostic::CorruptedEvent), PublicProjectionDeliveryConsumptionResult::DivergentPayload];
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $event = $this->event();
        $payload = new ProfessionalStatusDeliveryPayload($event);
        $source = PublicProjectionDeliverySourceModule::fromString('Professionals');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('ProfessionalStatus');
        $id = PublicProjectionDeliveryAggregateId::fromString($event->payload->professionalId->value);
        $type = PublicProjectionDeliveryEventType::fromString($event->metadata->eventType->value);
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($source, $aggregate, $id, 2, $index, $type, $version);

        return new PublicProjectionDeliveryMessage(PublicProjectionDeliveryMessageId::fromIdempotencyKey($key), $key, $type, $version, $source, $aggregate, $id, new PublicProjectionDeliveryOrder(2, $index), new DateTimeImmutable('2026-07-22T12:00:00Z'), new DateTimeImmutable('2026-07-22T12:00:01Z'), $payload);
    }

    private function event(): ProfessionalStatusEvent
    {
        $transition = new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend);
        $id = ProfessionalStatusId::fromString('a4100000-0000-4000-8000-000000000099');
        $version = ProfessionalStatusEventPayloadVersion::V1;
        $type = (new ProfessionalStatusEventCatalog)->typeFor($transition);
        $eventId = ProfessionalStatusEventId::derive($type, $version, $id, $transition, 2);
        $at = ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00Z'));

        return new ProfessionalStatusEvent(new ProfessionalStatusEventMetadata($type, $version, ProfessionalStatusActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at), new ProfessionalStatusEventPayload($eventId, $id, 'active>suspend>suspended', $transition->from, $transition->to, $transition->action, 1, 2));
    }
}

final class RecordingProfessionalStatusRouter implements ProfessionalStatusEventRouter
{
    /** @var list<ProfessionalStatusTransportEnvelope> */
    public array $envelopes = [];

    public function __construct(private readonly ProfessionalStatusEventRoutingResult $result) {}

    public function route(ProfessionalStatusTransportEnvelope $envelope): ProfessionalStatusEventRoutingResult
    {
        $this->envelopes[] = $envelope;

        return $this->result;
    }
}
