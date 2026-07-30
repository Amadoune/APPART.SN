<?php

namespace Tests\Unit\ReservationLifecycleEventTransport;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryIdempotencyKey;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\ReservationLifecycleEventConsumer\ReservationLifecycleDeliveryConsumer;
use App\Application\ReservationLifecycleEventConsumer\ReservationLifecycleDeliveryConsumptionPolicy;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleDeliveryPayload;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleEventRouterPort;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingResult;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingStatus;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportEnvelope;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleEvent\ReservationLifecycleEventCatalog;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReservationLifecycleDeliveryConsumerTest extends TestCase
{
    #[DataProvider('outcomes')]
    public function test_consumer_restores_routes_once_and_applies_the_certified_policy(ReservationLifecycleRoutingStatus $routing, PublicProjectionDeliveryConsumptionResult $expected): void
    {
        $router = new RecordingReservationRouter($routing);
        $message = $this->message();
        $result = (new ReservationLifecycleDeliveryConsumer($router, new ReservationLifecycleDeliveryConsumptionPolicy))->consume($message);

        self::assertSame($expected, $result);
        self::assertCount(1, $router->envelopes);
        self::assertSame($message->payload->fields()['canonicalEvent'], $router->envelopes[0]->payload->fields()['canonicalEvent']);
        self::assertSame($message->payload->checksum(), $router->envelopes[0]->metadata->payloadChecksum);
        self::assertSame($message->payload->event->payload->eventId->value, $router->envelopes[0]->metadata->businessEventId);
    }

    public static function outcomes(): iterable
    {
        yield [ReservationLifecycleRoutingStatus::Stored, PublicProjectionDeliveryConsumptionResult::Consumed];
        yield [ReservationLifecycleRoutingStatus::AlreadyStored, PublicProjectionDeliveryConsumptionResult::AlreadyConsumed];
        yield [ReservationLifecycleRoutingStatus::CorruptedEnvelope, PublicProjectionDeliveryConsumptionResult::DivergentPayload];
        yield [ReservationLifecycleRoutingStatus::PersistenceCorrupted, PublicProjectionDeliveryConsumptionResult::RetryableFailure];
    }

    public function test_divergent_delivery_metadata_never_reaches_the_router(): void
    {
        $router = new RecordingReservationRouter(ReservationLifecycleRoutingStatus::Stored);
        $message = $this->message();
        $divergent = new PublicProjectionDeliveryMessage($message->messageId, $message->idempotencyKey, $message->eventType, $message->payloadVersion, $message->sourceModule, PublicProjectionDeliveryAggregateType::fromString('Listing'), $message->aggregateId, $message->order, $message->occurredAt, $message->recordedAt, $message->payload);

        self::assertSame(PublicProjectionDeliveryConsumptionResult::DivergentPayload, (new ReservationLifecycleDeliveryConsumer($router, new ReservationLifecycleDeliveryConsumptionPolicy))->consume($divergent));
        self::assertSame([], $router->envelopes);
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $event = (new ReservationLifecycleEventCatalog)->eventFor(ReservationId::fromString('22222222-2222-4222-8222-222222222222'), new ReservationLifecycleTransition(ReservationLifecycleState::Draft, ReservationLifecycleState::Requested, ReservationLifecycleAction::Submit), 2);
        $payload = new ReservationLifecycleDeliveryPayload($event);
        $module = PublicProjectionDeliverySourceModule::fromString('ReservationLifecycle');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('ReservationLifecycle');
        $id = PublicProjectionDeliveryAggregateId::fromString($event->payload->reservationId->value);
        $type = PublicProjectionDeliveryEventType::fromString($event->metadata->eventType->value);
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($module, $aggregate, $id, 2, $index, $type, $version);

        return new PublicProjectionDeliveryMessage(PublicProjectionDeliveryMessageId::fromIdempotencyKey($key), $key, $type, $version, $module, $aggregate, $id, new PublicProjectionDeliveryOrder(2, $index), new DateTimeImmutable('2026-07-21T10:00:00Z'), new DateTimeImmutable('2026-07-21T10:00:01Z'), $payload);
    }
}

final class RecordingReservationRouter implements ReservationLifecycleEventRouterPort
{
    /** @var list<ReservationLifecycleTransportEnvelope> */
    public array $envelopes = [];

    public function __construct(private readonly ReservationLifecycleRoutingStatus $status) {}

    public function route(ReservationLifecycleTransportEnvelope $envelope): ReservationLifecycleRoutingResult
    {
        $this->envelopes[] = $envelope;

        return new ReservationLifecycleRoutingResult($this->status);
    }
}
