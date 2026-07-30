<?php

namespace Tests\Unit\AdministrationAudit;

use App\Application\AdministrativeActionLifecycleEventConsumption\AdministrativeActionLifecycleDeliveryConsumer;
use App\Application\AdministrativeActionLifecycleEventConsumption\AdministrativeActionLifecycleDeliveryConsumptionPolicy;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleDeliveryPayload;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRouter;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingDiagnostic;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleEventRoutingResult;
use App\Application\AdministrativeActionLifecycleEventTransport\AdministrativeActionLifecycleTransportEnvelope;
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
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionHistoricalMirror\AdministrativeActionHistoricalMirrorOccurredAt;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleAction;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleState;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle\AdministrativeActionLifecycleTransition;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEvent;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventId;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventMetadata;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayload;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventPayloadVersion;
use Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycleEvent\AdministrativeActionLifecycleEventType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdministrativeActionLifecycleOutboxCompatibilityTest extends TestCase
{
    public function test_catalog_accepts_exactly_four_administrative_action_lifecycle_events(): void
    {
        $catalog = new PublicProjectionDeliveryEventCatalog;
        $payload = new AdministrativeActionLifecycleDeliveryPayload($this->event());

        foreach (AdministrativeActionLifecycleEventType::cases() as $type) {
            self::assertTrue($catalog->accepts(
                PublicProjectionDeliveryEventType::fromString($type->value),
                PublicProjectionDeliveryPayloadVersion::fromInt(1),
                PublicProjectionDeliverySourceModule::fromString('AdministrationAudit'),
                PublicProjectionDeliveryAggregateType::fromString('AdministrativeActionLifecycle'),
                $payload,
            ));
        }
        self::assertCount(4, AdministrativeActionLifecycleEventType::cases());
    }

    #[DataProvider('outcomes')]
    public function test_consumer_routes_once_and_applies_certified_policy(
        AdministrativeActionLifecycleEventRoutingResult $routing,
        PublicProjectionDeliveryConsumptionResult $expected,
    ): void {
        $router = new RecordingAdministrativeActionLifecycleRouter($routing);
        $message = $this->message();
        $result = (new AdministrativeActionLifecycleDeliveryConsumer(
            $router,
            new AdministrativeActionLifecycleDeliveryConsumptionPolicy,
        ))->consume($message);

        self::assertSame($expected, $result);
        self::assertCount(1, $router->envelopes);
        self::assertSame($message->payload->fields(), $router->envelopes[0]->payload->fields());
    }

    public function test_divergent_metadata_never_reaches_router(): void
    {
        $router = new RecordingAdministrativeActionLifecycleRouter(AdministrativeActionLifecycleEventRoutingResult::routed());
        $message = $this->message();
        $divergent = new PublicProjectionDeliveryMessage(
            $message->messageId,
            $message->idempotencyKey,
            $message->eventType,
            $message->payloadVersion,
            $message->sourceModule,
            PublicProjectionDeliveryAggregateType::fromString('AdministrativeAction'),
            $message->aggregateId,
            $message->order,
            $message->occurredAt,
            $message->recordedAt,
            $message->payload,
        );

        self::assertSame(
            PublicProjectionDeliveryConsumptionResult::DivergentPayload,
            (new AdministrativeActionLifecycleDeliveryConsumer($router, new AdministrativeActionLifecycleDeliveryConsumptionPolicy))->consume($divergent),
        );
        self::assertSame([], $router->envelopes);
    }

    /** @return iterable<string,array{AdministrativeActionLifecycleEventRoutingResult,PublicProjectionDeliveryConsumptionResult}> */
    public static function outcomes(): iterable
    {
        yield 'routed' => [AdministrativeActionLifecycleEventRoutingResult::routed(), PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'deferred' => [AdministrativeActionLifecycleEventRoutingResult::deferred(), PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'retryable' => [AdministrativeActionLifecycleEventRoutingResult::retryableFailure(), PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'unsupported' => [AdministrativeActionLifecycleEventRoutingResult::rejected(AdministrativeActionLifecycleEventRoutingDiagnostic::UnsupportedEvent), PublicProjectionDeliveryConsumptionResult::UnsupportedEventType];
        yield 'corrupted' => [AdministrativeActionLifecycleEventRoutingResult::rejected(AdministrativeActionLifecycleEventRoutingDiagnostic::CorruptedEvent), PublicProjectionDeliveryConsumptionResult::DivergentPayload];
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $event = $this->event();
        $payload = new AdministrativeActionLifecycleDeliveryPayload($event);
        $source = PublicProjectionDeliverySourceModule::fromString('AdministrationAudit');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('AdministrativeActionLifecycle');
        $id = PublicProjectionDeliveryAggregateId::fromString($event->payload->actionId->value);
        $type = PublicProjectionDeliveryEventType::fromString($event->metadata->eventType->value);
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($source, $aggregate, $id, 2, $index, $type, $version);

        return new PublicProjectionDeliveryMessage(
            PublicProjectionDeliveryMessageId::fromIdempotencyKey($key),
            $key,
            $type,
            $version,
            $source,
            $aggregate,
            $id,
            new PublicProjectionDeliveryOrder(2, $index),
            new DateTimeImmutable('2026-07-24T12:00:00Z'),
            new DateTimeImmutable('2026-07-24T12:00:01Z'),
            $payload,
        );
    }

    private function event(): AdministrativeActionLifecycleEvent
    {
        $transition = new AdministrativeActionLifecycleTransition(
            AdministrativeActionLifecycleState::Draft,
            AdministrativeActionLifecycleState::Recorded,
            AdministrativeActionLifecycleAction::Record,
        );
        $id = AdministrativeActionId::fromString('a4700000-0000-4000-8000-000000000047');
        $version = AdministrativeActionLifecycleEventPayloadVersion::V1;
        $eventId = AdministrativeActionLifecycleEventId::derive(AdministrativeActionLifecycleEventType::Recorded, $version, $id, $transition, 2);
        $at = AdministrativeActionHistoricalMirrorOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-24T12:00:00Z'));

        return new AdministrativeActionLifecycleEvent(
            new AdministrativeActionLifecycleEventMetadata(AdministrativeActionLifecycleEventType::Recorded, $version, ActorId::fromString('decision-actor-001'), $at, $at),
            new AdministrativeActionLifecycleEventPayload($eventId, $id, 'draft>record>recorded', $transition->from, $transition->to, $transition->action, 1, 2),
        );
    }
}

final class RecordingAdministrativeActionLifecycleRouter implements AdministrativeActionLifecycleEventRouter
{
    /** @var list<AdministrativeActionLifecycleTransportEnvelope> */
    public array $envelopes = [];

    public function __construct(private readonly AdministrativeActionLifecycleEventRoutingResult $result) {}

    public function route(AdministrativeActionLifecycleTransportEnvelope $envelope): AdministrativeActionLifecycleEventRoutingResult
    {
        $this->envelopes[] = $envelope;

        return $this->result;
    }
}
