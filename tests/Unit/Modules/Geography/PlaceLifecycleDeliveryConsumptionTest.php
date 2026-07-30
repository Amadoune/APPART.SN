<?php

namespace Tests\Unit\Modules\Geography;

use App\Application\PlaceLifecycleEventConsumption\PlaceLifecycleDeliveryConsumer;
use App\Application\PlaceLifecycleEventConsumption\PlaceLifecycleDeliveryConsumptionPolicy;
use App\Application\PlaceLifecycleEventConsumption\PlaceLifecycleDeliveryConsumptionResult;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRouter;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingDiagnostic;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingResult;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleEventRoutingStatus;
use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportEnvelope;
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
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlaceLifecycleDeliveryConsumptionTest extends TestCase
{
    #[DataProvider('policyMatrix')]
    public function test_policy_is_closed(
        PlaceLifecycleEventRoutingStatus $status,
        PlaceLifecycleDeliveryConsumptionResult $expected,
    ): void {
        self::assertSame(
            $expected,
            (new PlaceLifecycleDeliveryConsumptionPolicy)->consumptionFor($status),
        );
    }

    public function test_consumer_delegates_without_business_decision(): void
    {
        $router = new class implements PlaceLifecycleEventRouter
        {
            public function route(PlaceLifecycleTransportEnvelope $envelope): PlaceLifecycleEventRoutingResult
            {
                return PlaceLifecycleEventRoutingResult::routed();
            }
        };
        $consumer = new PlaceLifecycleDeliveryConsumer(
            $router,
            new PlaceLifecycleDeliveryConsumptionPolicy,
        );

        self::assertSame(
            PlaceLifecycleDeliveryConsumptionResult::Acknowledged,
            $consumer->consumeEnvelope(DurablePlaceLifecycleEventRouterTest::envelope()),
        );
    }

    #[DataProvider('genericResultMatrix')]
    public function test_generic_boundary_applies_only_the_certified_r3_matrix(
        PlaceLifecycleEventRoutingStatus $status,
        PublicProjectionDeliveryConsumptionResult $expected,
    ): void {
        $router = new class($status) implements PlaceLifecycleEventRouter
        {
            public function __construct(private readonly PlaceLifecycleEventRoutingStatus $status) {}

            public function route(PlaceLifecycleTransportEnvelope $envelope): PlaceLifecycleEventRoutingResult
            {
                return match ($this->status) {
                    PlaceLifecycleEventRoutingStatus::Routed => PlaceLifecycleEventRoutingResult::routed(),
                    PlaceLifecycleEventRoutingStatus::Deferred => PlaceLifecycleEventRoutingResult::deferred(),
                    PlaceLifecycleEventRoutingStatus::RetryableFailure => PlaceLifecycleEventRoutingResult::retryableFailure(),
                    PlaceLifecycleEventRoutingStatus::Rejected => PlaceLifecycleEventRoutingResult::rejected(
                        PlaceLifecycleEventRoutingDiagnostic::CorruptedEvent,
                    ),
                };
            }
        };

        self::assertSame(
            $expected,
            (new PlaceLifecycleDeliveryConsumer($router, new PlaceLifecycleDeliveryConsumptionPolicy))
                ->consume(self::genericMessage()),
        );
    }

    /** @return iterable<string, array{PlaceLifecycleEventRoutingStatus,PlaceLifecycleDeliveryConsumptionResult}> */
    public static function policyMatrix(): iterable
    {
        yield 'routed' => [PlaceLifecycleEventRoutingStatus::Routed, PlaceLifecycleDeliveryConsumptionResult::Acknowledged];
        yield 'deferred' => [PlaceLifecycleEventRoutingStatus::Deferred, PlaceLifecycleDeliveryConsumptionResult::Retry];
        yield 'retryable' => [PlaceLifecycleEventRoutingStatus::RetryableFailure, PlaceLifecycleDeliveryConsumptionResult::Retry];
        yield 'rejected' => [PlaceLifecycleEventRoutingStatus::Rejected, PlaceLifecycleDeliveryConsumptionResult::Quarantined];
    }

    /** @return iterable<string, array{PlaceLifecycleEventRoutingStatus,PublicProjectionDeliveryConsumptionResult}> */
    public static function genericResultMatrix(): iterable
    {
        yield 'acknowledged' => [PlaceLifecycleEventRoutingStatus::Routed, PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'retry deferred' => [PlaceLifecycleEventRoutingStatus::Deferred, PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'retry failure' => [PlaceLifecycleEventRoutingStatus::RetryableFailure, PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'quarantined' => [PlaceLifecycleEventRoutingStatus::Rejected, PublicProjectionDeliveryConsumptionResult::PermanentFailure];
    }

    private static function genericMessage(): PublicProjectionDeliveryMessage
    {
        $envelope = DurablePlaceLifecycleEventRouterTest::envelope();
        $event = $envelope->payload->event;
        $source = PublicProjectionDeliverySourceModule::fromString('Geography');
        $aggregate = PublicProjectionDeliveryAggregateType::fromString('PlaceLifecycle');
        $id = PublicProjectionDeliveryAggregateId::fromString($event->payload->placeId->value);
        $type = PublicProjectionDeliveryEventType::fromString($event->type->value);
        $version = PublicProjectionDeliveryPayloadVersion::fromInt(1);
        $index = PublicProjectionDeliveryEventIndex::fromInt(1);
        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents(
            $source,
            $aggregate,
            $id,
            $event->payload->occurredVersion,
            $index,
            $type,
            $version,
        );

        return new PublicProjectionDeliveryMessage(
            PublicProjectionDeliveryMessageId::fromIdempotencyKey($key),
            $key,
            $type,
            $version,
            $source,
            $aggregate,
            $id,
            new PublicProjectionDeliveryOrder($event->payload->occurredVersion, $index),
            new DateTimeImmutable($event->occurredAt->canonical()),
            new DateTimeImmutable($event->occurredAt->canonical()),
            $envelope->payload,
        );
    }
}
