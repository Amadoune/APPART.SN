<?php

namespace Tests\Unit\PublicSearchDecisionMaterialization;

use App\Application\ListingPublicationEventConsumer\ListingPublicationEventDeliveryConsumer;
use App\Application\ListingPublicationEventTransport\Contract\ListingPublicationEventRouter;
use App\Application\ListingPublicationEventTransport\ListingPublicationDeliveryPayload;
use App\Application\ListingPublicationEventTransport\ListingPublicationEventRoutingResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryCatalogMessageFactory;
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
use Appart\Modules\ContentSeo\Application\Materialization\ContentSeoMaterializationStatus;
use Appart\Modules\ContentSeo\Application\Materialization\Contract\MaterializeContentSeoSnapshotV1;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId as ContentSeoListingId;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEvent;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventCatalog;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventInstant;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event\ListingPublicationEventMetadata;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationTransition;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId as LifecycleListingId;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\MaterializePublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationResult;
use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchMaterializationStatus;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ListingPublishedSearchHandoffTest extends TestCase
{
    #[DataProvider('resultProvider')]
    public function test_published_delivery_materializes_search_before_acknowledgement(PublicSearchMaterializationStatus $status, PublicProjectionDeliveryConsumptionResult $expected): void
    {
        $materializer = new RecordingMaterializer($status);
        $result = (new ListingPublicationEventDeliveryConsumer(new RoutedEventRouter, $materializer, new AppliedContentSeoMaterializer))->consume($this->message());

        self::assertSame($expected, $result);
        self::assertSame('11111111-1111-4111-8111-111111111111', $materializer->listingId?->value);
    }

    public static function resultProvider(): iterable
    {
        yield 'applied' => [PublicSearchMaterializationStatus::Applied, PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'replay' => [PublicSearchMaterializationStatus::AlreadyApplied, PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'missing' => [PublicSearchMaterializationStatus::SourceMissing, PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'unavailable' => [PublicSearchMaterializationStatus::DependencyUnavailable, PublicProjectionDeliveryConsumptionResult::RetryableFailure];
        yield 'divergent' => [PublicSearchMaterializationStatus::Divergent, PublicProjectionDeliveryConsumptionResult::PermanentFailure];
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $event = (new ListingPublicationEventCatalog)->eventsFor(
            LifecycleListingId::fromString('11111111-1111-4111-8111-111111111111'),
            new ListingPublicationTransition(ListingPublicationState::UnderReview, ListingPublicationState::Published, ListingPublicationAction::ApproveAndPublish),
            4,
            new ListingPublicationEventMetadata(ListingPublicationEventInstant::fromCanonicalUtc('2026-08-15T07:22:24.000000Z'), ListingPublicationEventInstant::fromCanonicalUtc('2026-08-15T07:22:25.000000Z')),
        )[0];
        $fact = new PublicProjectionDeliveryPublishableFact(
            PublicProjectionDeliveryEventType::fromString($event->type->value),
            PublicProjectionDeliveryPayloadVersion::fromInt($event->payloadVersion->value),
            PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'),
            PublicProjectionDeliveryAggregateType::fromString('Listing'),
            PublicProjectionDeliveryAggregateId::fromString($event->payload->listingId->value),
            new PublicProjectionDeliveryOrder(4, PublicProjectionDeliveryEventIndex::fromInt(1)),
            new DateTimeImmutable($event->metadata->occurredAt->value),
            new ListingPublicationDeliveryPayload($event),
        );

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable($event->metadata->recordedAt->value));
    }
}

final readonly class AppliedContentSeoMaterializer implements MaterializeContentSeoSnapshotV1
{
    public function materialize(ContentSeoListingId $listingId): ContentSeoMaterializationResult
    {
        return new ContentSeoMaterializationResult(ContentSeoMaterializationStatus::Applied);
    }
}

final readonly class RoutedEventRouter implements ListingPublicationEventRouter
{
    public function route(ListingPublicationEvent $event): ListingPublicationEventRoutingResult
    {
        return ListingPublicationEventRoutingResult::routed();
    }
}

final class RecordingMaterializer implements MaterializePublicSearchDecisionV1
{
    public ?ListingId $listingId = null;

    public function __construct(private readonly PublicSearchMaterializationStatus $status) {}

    public function materialize(ListingId $listingId): PublicSearchMaterializationResult
    {
        $this->listingId = $listingId;

        return PublicSearchMaterializationResult::of($this->status);
    }
}
