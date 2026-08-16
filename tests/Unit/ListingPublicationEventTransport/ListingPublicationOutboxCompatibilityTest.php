<?php

namespace Tests\Unit\ListingPublicationEventTransport;

use App\Application\ListingPublicationEventConsumer\ListingPublicationEventDeliveryConsumer;
use App\Application\ListingPublicationEventTransport\Contract\ListingPublicationEventRouter;
use App\Application\ListingPublicationEventTransport\ListingPublicationDeliveryPayload;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingDiagnosticCode;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingResult;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryContentSeoPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryMediaPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryPropertyPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliverySearchPayload;
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
use Appart\Modules\ContentSeo\Application\Materialization\ContentSeoMaterializationResult;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\MaterializeContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId as ContentSeoListingId;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventType;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\MaterializePublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationResult;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId as SearchListingId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ListingPublicationOutboxCompatibilityTest extends TestCase
{
    public function test_catalog_recognizes_all_fifteen_business_events(): void
    {
        $catalog = new PublicProjectionDeliveryEventCatalog;
        $event = $this->event();
        $payload = new ListingPublicationDeliveryPayload($event);
        foreach (ListingPublicationEventType::cases() as $type) {
            $deliveryType = PublicProjectionDeliveryEventType::fromString($type->value);
            self::assertSame(PublicProjectionDeliveryCompatibility::Supported, $catalog->compatibility($deliveryType, PublicProjectionDeliveryPayloadVersion::fromInt(1)));
            self::assertTrue($catalog->accepts($deliveryType, PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'), PublicProjectionDeliveryAggregateType::fromString('Listing'), $payload));
        }
    }

    public function test_five_historical_catalog_messages_remain_supported(): void
    {
        $catalog = new PublicProjectionDeliveryEventCatalog;
        $cases = [
            ['listing.reconstruction.requested', 'ListingLifecycle', 'Listing', new PublicProjectionDeliveryListingPayload('listing:1')],
            ['property.reconstruction.requested', 'RealEstateCatalog', 'Property', new PublicProjectionDeliveryPropertyPayload('property:1')],
            ['media.reconstruction.requested', 'Media', 'MediaCollection', new PublicProjectionDeliveryMediaPayload('media:1')],
            ['search.reconstruction.requested', 'SearchDiscovery', 'SearchIndex', new PublicProjectionDeliverySearchPayload('listing:1')],
            ['content_seo.reconstruction.requested', 'ContentSeo', 'SeoProjection', new PublicProjectionDeliveryContentSeoPayload('listing:1')],
        ];
        foreach ($cases as [$type, $module, $aggregate, $payload]) {
            self::assertTrue($catalog->accepts(PublicProjectionDeliveryEventType::fromString($type), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString($module), PublicProjectionDeliveryAggregateType::fromString($aggregate), $payload));
        }
    }

    public function test_unknown_catalog_type_is_explicitly_unsupported(): void
    {
        self::assertSame(
            PublicProjectionDeliveryCompatibility::UnsupportedType,
            (new PublicProjectionDeliveryEventCatalog)->compatibility(PublicProjectionDeliveryEventType::fromString('listing.publication.unknown'), PublicProjectionDeliveryPayloadVersion::fromInt(1)),
        );
    }

    #[DataProvider('consumerOutcomeProvider')]
    public function test_consumer_restores_and_routes_exactly_once(ListingPublicationEventRoutingResult $routing, PublicProjectionDeliveryConsumptionResult $expected): void
    {
        $router = new ConsumerRouterSpy($routing);
        $message = $this->message();
        $result = (new ListingPublicationEventDeliveryConsumer($router, new UnusedSearchDecisionMaterializer, new UnusedContentSeoMaterializer))->consume($message);

        self::assertSame($expected, $result);
        self::assertEquals($this->event(), $router->received);
        self::assertSame(1, $router->calls);
    }

    /** @return iterable<string, array{ListingPublicationEventRoutingResult,PublicProjectionDeliveryConsumptionResult}> */
    public static function consumerOutcomeProvider(): iterable
    {
        yield 'routed' => [ListingPublicationEventRoutingResult::routed(), PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'deferred' => [ListingPublicationEventRoutingResult::deferred(ListingPublicationEventRoutingDiagnosticCode::RouteUnavailable), PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'retry' => [ListingPublicationEventRoutingResult::retryableFailure(ListingPublicationEventRoutingDiagnosticCode::TransferFailed), PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'rejected' => [ListingPublicationEventRoutingResult::rejected(ListingPublicationEventRoutingDiagnosticCode::CorruptedEvent), PublicProjectionDeliveryConsumptionResult::PermanentFailure];
    }

    public function test_consumer_rejects_an_incompatible_payload_without_routing(): void
    {
        $router = new ConsumerRouterSpy(ListingPublicationEventRoutingResult::routed());
        $message = $this->message();
        $invalid = new PublicProjectionDeliveryMessage($message->messageId, $message->idempotencyKey, $message->eventType, $message->payloadVersion, $message->sourceModule, $message->aggregateType, $message->aggregateId, $message->order, $message->occurredAt, $message->recordedAt, new PublicProjectionDeliveryListingPayload($message->aggregateId->value));

        self::assertSame(PublicProjectionDeliveryConsumptionResult::DivergentPayload, (new ListingPublicationEventDeliveryConsumer($router, new UnusedSearchDecisionMaterializer, new UnusedContentSeoMaterializer))->consume($invalid));
        self::assertSame(0, $router->calls);
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $event = $this->event();
        $fact = new PublicProjectionDeliveryPublishableFact(
            PublicProjectionDeliveryEventType::fromString($event->type->value),
            PublicProjectionDeliveryPayloadVersion::fromInt($event->payloadVersion->value),
            PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'),
            PublicProjectionDeliveryAggregateType::fromString('Listing'),
            PublicProjectionDeliveryAggregateId::fromString($event->payload->listingId->value),
            new PublicProjectionDeliveryOrder($event->payload->publicationVersion, PublicProjectionDeliveryEventIndex::fromInt(1)),
            new DateTimeImmutable($event->metadata->occurredAt->value),
            new ListingPublicationDeliveryPayload($event),
        );

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable($event->metadata->recordedAt->value));
    }

    private function event(): ListingPublicationEvent
    {
        return (new ListingPublicationEventCatalog)->eventsFor(
            ListingId::fromString('11111111-1111-4111-8111-111111111111'),
            new ListingPublicationTransition(ListingPublicationState::Draft, ListingPublicationState::Submitted, ListingPublicationAction::Submit),
            2,
            new ListingPublicationEventMetadata(
                ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:00.000000Z'),
                ListingPublicationEventInstant::fromCanonicalUtc('2026-07-20T10:00:01.000000Z'),
            ),
        )[0];
    }
}

final readonly class UnusedContentSeoMaterializer implements MaterializeContentSeoSnapshotV1
{
    public function materialize(ContentSeoListingId $listingId): ContentSeoMaterializationResult
    {
        throw new \LogicException('The ContentSeo materializer must not be called for a non-Published event.');
    }
}

final class ConsumerRouterSpy implements ListingPublicationEventRouter
{
    public int $calls = 0;

    public ?ListingPublicationEvent $received = null;

    public function __construct(private readonly ListingPublicationEventRoutingResult $result) {}

    public function route(ListingPublicationEvent $event): ListingPublicationEventRoutingResult
    {
        $this->calls++;
        $this->received = $event;

        return $this->result;
    }
}

final readonly class UnusedSearchDecisionMaterializer implements MaterializePublicSearchDecisionV1
{
    public function materialize(SearchListingId $listingId): PublicSearchMaterializationResult
    {
        throw new \LogicException('The Search materializer must not be called for a non-Published event.');
    }
}
