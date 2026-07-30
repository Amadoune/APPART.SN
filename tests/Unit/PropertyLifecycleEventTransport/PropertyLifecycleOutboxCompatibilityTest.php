<?php

namespace Tests\Unit\PropertyLifecycleEventTransport;

use App\Application\PropertyLifecycleEventConsumer\PropertyLifecycleEventDeliveryConsumer;
use App\Application\PropertyLifecycleEventTransport\Contract\PropertyLifecycleEventRouter;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleDeliveryPayload;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventRoutingDiagnosticCode;
use App\Application\PropertyLifecycleEventTransport\PropertyLifecycleEventRoutingResult;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCompatibility;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventCatalog;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventIndex;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPublishableFact;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEvent;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventCatalog;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventInstant;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventMetadata;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event\PropertyLifecycleEventType;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleAction;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleState;
use Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\PropertyLifecycleTransition;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PropertyLifecycleOutboxCompatibilityTest extends TestCase
{
    public function test_catalog_recognizes_all_seven_property_lifecycle_events_without_regressing_existing_entries(): void
    {
        $catalog = new PublicProjectionDeliveryEventCatalog;
        $payload = new PropertyLifecycleDeliveryPayload($this->event());
        foreach (PropertyLifecycleEventType::cases() as $type) {
            $deliveryType = PublicProjectionDeliveryEventType::fromString($type->value);
            self::assertSame(PublicProjectionDeliveryCompatibility::Supported, $catalog->compatibility($deliveryType, PublicProjectionDeliveryPayloadVersion::fromInt(1)));
            self::assertTrue($catalog->accepts($deliveryType, PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('RealEstateCatalog'), PublicProjectionDeliveryAggregateType::fromString('Property'), $payload));
        }
        self::assertTrue($catalog->accepts(PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'), PublicProjectionDeliveryAggregateType::fromString('Listing'), new PublicProjectionDeliveryListingPayload('listing:1')));
        self::assertSame(PublicProjectionDeliveryCompatibility::UnsupportedType, $catalog->compatibility(PublicProjectionDeliveryEventType::fromString('property.lifecycle.unknown'), PublicProjectionDeliveryPayloadVersion::fromInt(1)));
    }

    #[DataProvider('consumerOutcomeProvider')]
    public function test_consumer_restores_routes_once_and_maps_closed_outcome(PropertyLifecycleEventRoutingResult $routing, PublicProjectionDeliveryConsumptionResult $expected): void
    {
        $router = new PropertyLifecycleConsumerRouterSpy($routing);
        $result = (new PropertyLifecycleEventDeliveryConsumer($router))->consume($this->message());

        self::assertSame($expected, $result);
        self::assertEquals($this->event(), $router->received);
        self::assertSame(1, $router->calls);
    }

    /** @return iterable<string, array{PropertyLifecycleEventRoutingResult,PublicProjectionDeliveryConsumptionResult}> */
    public static function consumerOutcomeProvider(): iterable
    {
        yield 'routed' => [PropertyLifecycleEventRoutingResult::routed(), PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'deferred' => [PropertyLifecycleEventRoutingResult::deferred(PropertyLifecycleEventRoutingDiagnosticCode::RouteUnavailable), PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'retryable failure' => [PropertyLifecycleEventRoutingResult::retryableFailure(PropertyLifecycleEventRoutingDiagnosticCode::TransferFailed), PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'rejected' => [PropertyLifecycleEventRoutingResult::rejected(PropertyLifecycleEventRoutingDiagnosticCode::CorruptedEvent), PublicProjectionDeliveryConsumptionResult::PermanentFailure];
    }

    public function test_consumer_rejects_incompatible_payload_without_routing(): void
    {
        $router = new PropertyLifecycleConsumerRouterSpy(PropertyLifecycleEventRoutingResult::routed());
        $message = $this->message();
        $invalid = new PublicProjectionDeliveryMessage($message->messageId, $message->idempotencyKey, $message->eventType, $message->payloadVersion, $message->sourceModule, $message->aggregateType, $message->aggregateId, $message->order, $message->occurredAt, $message->recordedAt, new PublicProjectionDeliveryListingPayload('listing:1'));

        self::assertSame(PublicProjectionDeliveryConsumptionResult::DivergentPayload, (new PropertyLifecycleEventDeliveryConsumer($router))->consume($invalid));
        self::assertSame(0, $router->calls);
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $event = $this->event();
        $fact = new PublicProjectionDeliveryPublishableFact(
            PublicProjectionDeliveryEventType::fromString($event->type->value),
            PublicProjectionDeliveryPayloadVersion::fromInt($event->payloadVersion->value),
            PublicProjectionDeliverySourceModule::fromString('RealEstateCatalog'),
            PublicProjectionDeliveryAggregateType::fromString('Property'),
            PublicProjectionDeliveryAggregateId::fromString($event->payload->propertyId->value),
            new PublicProjectionDeliveryOrder($event->payload->lifecycleVersion, PublicProjectionDeliveryEventIndex::fromInt(1)),
            new DateTimeImmutable($event->metadata->occurredAt->value),
            new PropertyLifecycleDeliveryPayload($event),
        );

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable($event->metadata->recordedAt->value));
    }

    private function event(): PropertyLifecycleEvent
    {
        return (new PropertyLifecycleEventCatalog)->eventsFor(
            PropertyId::fromString('22222222-2222-4222-8222-222222222222'),
            new PropertyLifecycleTransition(PropertyLifecycleState::Draft, PropertyLifecycleState::Active, PropertyLifecycleAction::Activate),
            2,
            new PropertyLifecycleEventMetadata(
                PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:00.000000Z'),
                PropertyLifecycleEventInstant::fromCanonicalUtc('2026-07-21T10:00:01.000000Z'),
            ),
        )[0];
    }
}

final class PropertyLifecycleConsumerRouterSpy implements PropertyLifecycleEventRouter
{
    public int $calls = 0;

    public ?PropertyLifecycleEvent $received = null;

    public function __construct(private readonly PropertyLifecycleEventRoutingResult $result) {}

    public function route(PropertyLifecycleEvent $event): PropertyLifecycleEventRoutingResult
    {
        $this->calls++;
        $this->received = $event;

        return $this->result;
    }
}
