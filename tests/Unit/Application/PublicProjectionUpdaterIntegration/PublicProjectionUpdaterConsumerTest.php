<?php

namespace Tests\Unit\Application\PublicProjectionUpdaterIntegration;

use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryListingPayload;
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
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionRetry\PublicProjectionDeterministicRetryPolicy;
use App\Application\PublicProjectionRetry\PublicProjectionFixedBackoff;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateOutcome;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolution;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolutionStatus;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolver;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionUpdaterConsumer;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistration;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistry;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryOutcome;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorker;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorkerId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Application\PublicProjectionUpdaterIntegration\Support\FakePublicProjectionSourceLookup;
use Tests\Unit\Application\PublicProjectionUpdaterIntegration\Support\FakePublicProjectionUpdateExecutor;
use Tests\Unit\Application\PublicProjectionWorker\Support\FakePublicProjectionDeliveryClock;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxClaimManager;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxReader;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxState;
use Tests\Unit\Contracts\PublicProjectionOutbox\Support\FakePublicProjectionOutboxWriter;

final class PublicProjectionUpdaterConsumerTest extends TestCase
{
    #[DataProvider('outcomes')]
    public function test_every_updater_outcome_is_mapped_explicitly(PublicListingProjectionUpdateOutcome $outcome, PublicProjectionDeliveryConsumptionResult $expected): void
    {
        $updater = new FakePublicProjectionUpdateExecutor($outcome);
        $consumer = $this->consumer(new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::Resolved, self::listingId()), $updater);

        self::assertSame($expected, $consumer->consume($this->message()));
        self::assertSame(1, $updater->calls);
    }

    public static function outcomes(): iterable
    {
        yield 'applied' => [PublicListingProjectionUpdateOutcome::Applied, PublicProjectionDeliveryConsumptionResult::Consumed];
        yield 'already' => [PublicListingProjectionUpdateOutcome::AlreadyApplied, PublicProjectionDeliveryConsumptionResult::AlreadyConsumed];
        yield 'obsolete' => [PublicListingProjectionUpdateOutcome::RejectedObsolete, PublicProjectionDeliveryConsumptionResult::RejectedObsolete];
        yield 'source unavailable' => [PublicListingProjectionUpdateOutcome::SourceUnavailable, PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'projection unavailable' => [PublicListingProjectionUpdateOutcome::ProjectionUnavailable, PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'promotion not ready' => [PublicListingProjectionUpdateOutcome::PromotionNotReady, PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'incomplete watermark' => [PublicListingProjectionUpdateOutcome::IncompleteWatermark, PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness];
        yield 'divergent watermark' => [PublicListingProjectionUpdateOutcome::DivergentWatermark, PublicProjectionDeliveryConsumptionResult::DivergentPayload];
        yield 'canonical collision' => [PublicListingProjectionUpdateOutcome::CanonicalCollision, PublicProjectionDeliveryConsumptionResult::PermanentFailure];
        yield 'canonical replacement' => [PublicListingProjectionUpdateOutcome::CanonicalReplacementRequired, PublicProjectionDeliveryConsumptionResult::PermanentFailure];
        yield 'historical reservation' => [PublicListingProjectionUpdateOutcome::HistoricalReservationConflict, PublicProjectionDeliveryConsumptionResult::PermanentFailure];
        yield 'generation mismatch' => [PublicListingProjectionUpdateOutcome::GenerationMismatch, PublicProjectionDeliveryConsumptionResult::PermanentFailure];
    }

    #[DataProvider('readinessBlockages')]
    public function test_resolution_blockages_never_call_updater(PublicProjectionSourceResolutionStatus $status): void
    {
        $updater = new FakePublicProjectionUpdateExecutor(PublicListingProjectionUpdateOutcome::Applied);
        $consumer = $this->consumer(new PublicProjectionSourceResolution($status), $updater);

        self::assertSame(PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness, $consumer->consume($this->message()));
        self::assertSame(0, $updater->calls);
    }

    public static function readinessBlockages(): iterable
    {
        yield 'source unavailable' => [PublicProjectionSourceResolutionStatus::SourceUnavailable];
        yield 'promotion not ready' => [PublicProjectionSourceResolutionStatus::PromotionNotReady];
        yield 'watermark incomplete' => [PublicProjectionSourceResolutionStatus::WatermarkIncomplete];
        yield 'public geography' => [PublicProjectionSourceResolutionStatus::MissingPublicGeographyRevision];
        yield 'public media' => [PublicProjectionSourceResolutionStatus::MissingPublicMediaRevision];
        yield 'public geography and media' => [PublicProjectionSourceResolutionStatus::MissingPublicGeographyAndMediaRevisions];
    }

    public function test_worker_consumer_updater_pipeline_is_idempotently_delivered(): void
    {
        $updater = new FakePublicProjectionUpdateExecutor(PublicListingProjectionUpdateOutcome::Applied);
        $consumer = $this->consumer(new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::Resolved, self::listingId()), $updater);
        $state = new FakePublicProjectionOutboxState;
        $writer = new FakePublicProjectionOutboxWriter($state);
        $consumerId = PublicProjectionOutboxConsumerId::fromString('public-projection');
        $registry = new PublicProjectionDeliveryConsumerRegistry([
            new PublicProjectionDeliveryConsumerRegistration($consumerId, PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), $consumer),
        ]);
        $worker = new PublicProjectionDeliveryWorker(new FakePublicProjectionOutboxReader($state), new FakePublicProjectionOutboxClaimManager($state), $writer, $registry, new PublicProjectionDeterministicRetryPolicy(3, new PublicProjectionFixedBackoff(1)), new FakePublicProjectionDeliveryClock(new DateTimeImmutable('2026-07-19T12:00:00+00:00')), PublicProjectionDeliveryWorkerId::fromString('worker:integration'), 10, 60);
        $writer->append($this->message(), $consumerId);

        $result = $worker->runOnce($consumerId);

        self::assertSame(1, $result->count(PublicProjectionDeliveryOutcome::Delivered));
        self::assertSame(1, $updater->calls);
        self::assertSame(0, $worker->runOnce($consumerId)->requested);
    }

    private function consumer(PublicProjectionSourceResolution $resolution, FakePublicProjectionUpdateExecutor $updater): PublicProjectionUpdaterConsumer
    {
        return new PublicProjectionUpdaterConsumer(new PublicProjectionSourceResolver(new PublicProjectionDeliveryEventCatalog, new FakePublicProjectionSourceLookup($resolution)), $updater);
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString('listing.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('ListingLifecycle'), PublicProjectionDeliveryAggregateType::fromString('Listing'), PublicProjectionDeliveryAggregateId::fromString(self::listingId()), new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)), new DateTimeImmutable('2026-07-19T10:00:00+00:00'), new PublicProjectionDeliveryListingPayload(self::listingId()));

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable('2026-07-19T10:01:00+00:00'));
    }

    private static function listingId(): string
    {
        return '43000000-0000-4000-8000-000000000001';
    }
}
