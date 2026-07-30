<?php

namespace Tests\Unit\LeadLifecycleEventTransport;

use App\Application\LeadLifecycleEventConsumption\LeadLifecycleDeliveryConsumer;
use App\Application\LeadLifecycleEventConsumption\LeadLifecycleDeliveryConsumptionPolicy;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleDeliveryPayload;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRouter;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingDiagnostic;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleEventRoutingResult;
use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportEnvelope;
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
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEvent;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventCatalog;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventMetadata;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayload;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventPayloadVersion;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent\LeadLifecycleEventType;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LeadLifecycleOutboxCompatibilityTest extends TestCase
{
    public function test_catalog_accepts_exactly_the_three_lead_event_types(): void
    {
        $catalog = new PublicProjectionDeliveryEventCatalog;
        $payload = new LeadLifecycleDeliveryPayload($this->event());

        foreach (LeadLifecycleEventType::cases() as $type) {
            self::assertTrue($catalog->accepts(
                PublicProjectionDeliveryEventType::fromString($type->value),
                PublicProjectionDeliveryPayloadVersion::fromInt(1),
                PublicProjectionDeliverySourceModule::fromString('ContactsLeads'),
                PublicProjectionDeliveryAggregateType::fromString('LeadLifecycle'),
                $payload,
            ));
        }
        self::assertCount(3, LeadLifecycleEventType::cases());
    }

    #[DataProvider('outcomes')]
    public function test_consumer_restores_routes_once_and_applies_certified_policy(
        LeadLifecycleEventRoutingResult $routing,
        PublicProjectionDeliveryConsumptionResult $expected,
    ): void {
        $router = new RecordingLeadRouter($routing);
        $message = $this->message();
        $result = (new LeadLifecycleDeliveryConsumer($router, new LeadLifecycleDeliveryConsumptionPolicy))->consume($message);

        self::assertSame($expected, $result);
        self::assertCount(1, $router->envelopes);
        self::assertSame($message->payload->fields()['canonicalEvent'], $router->envelopes[0]->payload->fields()['canonicalEvent']);
    }

    public function test_divergent_delivery_metadata_never_reaches_router(): void
    {
        $router = new RecordingLeadRouter(LeadLifecycleEventRoutingResult::routed());
        $message = $this->message();
        $divergent = new PublicProjectionDeliveryMessage($message->messageId, $message->idempotencyKey, $message->eventType, $message->payloadVersion, $message->sourceModule, PublicProjectionDeliveryAggregateType::fromString('Listing'), $message->aggregateId, $message->order, $message->occurredAt, $message->recordedAt, $message->payload);

        self::assertSame(PublicProjectionDeliveryConsumptionResult::DivergentPayload, (new LeadLifecycleDeliveryConsumer($router, new LeadLifecycleDeliveryConsumptionPolicy))->consume($divergent));
        self::assertSame([], $router->envelopes);
    }

    /** @return iterable<string, array{LeadLifecycleEventRoutingResult, PublicProjectionDeliveryConsumptionResult}> */
    public static function outcomes(): iterable
    {
        yield 'routed' => [LeadLifecycleEventRoutingResult::routed(), PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'deferred' => [LeadLifecycleEventRoutingResult::deferred(), PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'retryable' => [LeadLifecycleEventRoutingResult::retryableFailure(), PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'unsupported' => [LeadLifecycleEventRoutingResult::rejected(LeadLifecycleEventRoutingDiagnostic::UnsupportedEvent), PublicProjectionDeliveryConsumptionResult::UnsupportedEventType];
        yield 'corrupted' => [LeadLifecycleEventRoutingResult::rejected(LeadLifecycleEventRoutingDiagnostic::CorruptedEvent), PublicProjectionDeliveryConsumptionResult::DivergentPayload];
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $event = $this->event();
        $payload = new LeadLifecycleDeliveryPayload($event);
        $source = PublicProjectionDeliverySourceModule::fromString('ContactsLeads');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('LeadLifecycle');
        $id = PublicProjectionDeliveryAggregateId::fromString($event->payload->leadId->value);
        $type = PublicProjectionDeliveryEventType::fromString($event->metadata->eventType->value);
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($source, $aggregate, $id, 2, $index, $type, $version);

        return new PublicProjectionDeliveryMessage(PublicProjectionDeliveryMessageId::fromIdempotencyKey($key), $key, $type, $version, $source, $aggregate, $id, new PublicProjectionDeliveryOrder(2, $index), new DateTimeImmutable('2026-07-22T12:00:00Z'), new DateTimeImmutable('2026-07-22T12:00:01Z'), $payload);
    }

    private function event(): LeadLifecycleEvent
    {
        $transition = new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver);
        $leadId = LeadId::fromString('a4100000-0000-4000-8000-000000000098');
        $version = LeadLifecycleEventPayloadVersion::V1;
        $type = (new LeadLifecycleEventCatalog)->typeFor($transition);
        $eventId = LeadLifecycleEventId::derive($type, $version, $leadId, $transition, 2);
        $at = LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00Z'));

        return new LeadLifecycleEvent(
            new LeadLifecycleEventMetadata($type, $version, LeadLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $at, $at),
            new LeadLifecycleEventPayload($eventId, $leadId, 'created>deliver>delivered', $transition->from, $transition->to, $transition->action, 1, 2),
        );
    }
}

final class RecordingLeadRouter implements LeadLifecycleEventRouter
{
    /** @var list<LeadLifecycleTransportEnvelope> */
    public array $envelopes = [];

    public function __construct(private readonly LeadLifecycleEventRoutingResult $result) {}

    public function route(LeadLifecycleTransportEnvelope $envelope): LeadLifecycleEventRoutingResult
    {
        $this->envelopes[] = $envelope;

        return $this->result;
    }
}
