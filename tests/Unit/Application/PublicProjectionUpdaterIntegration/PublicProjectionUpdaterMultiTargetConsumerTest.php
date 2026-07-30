<?php

namespace Tests\Unit\Application\PublicProjectionUpdaterIntegration;

use App\Application\MultiTargetDelivery\Contract\MultiTargetPropagationStrategy;
use App\Application\MultiTargetDelivery\MultiTargetPropagationPlan;
use App\Application\MultiTargetDelivery\MultiTargetPropagationRequest;
use App\Application\MultiTargetDelivery\MultiTargetPropagationSource;
use App\Application\MultiTargetDelivery\MultiTargetPropagationStatus;
use App\Application\PropertyListingResolution\PropertyListingsDiagnostic;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryMediaPayload;
use App\Application\PublicProjectionDelivery\Payload\PublicProjectionDeliveryPropertyPayload;
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
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateOutcome;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateResult;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionUpdateExecutor;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceDiagnostic;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolution;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolutionStatus;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionSourceResolver;
use App\Application\PublicProjectionUpdaterIntegration\PublicProjectionUpdaterConsumer;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Application\PublicProjectionUpdaterIntegration\Support\FakePublicProjectionSourceLookup;

final class PublicProjectionUpdaterMultiTargetConsumerTest extends TestCase
{
    private const string PROPERTY = '9b000000-0000-4000-8000-000000000001';

    public function test_no_target_is_a_terminal_success_without_updater_call(): void
    {
        [$consumer, $updater] = $this->consumer([$this->plan(MultiTargetPropagationStatus::NoTargets, [], null, true)]);
        self::assertSame(PublicProjectionDeliveryConsumptionResult::Consumed, $consumer->consume($this->message()));
        self::assertSame([], $updater->calls);
    }

    public function test_all_pages_and_targets_are_processed_in_stable_order_before_success(): void
    {
        [$consumer, $updater] = $this->consumer([
            $this->plan(MultiTargetPropagationStatus::TargetsAvailable, ['listing-1', 'listing-2'], 'next', false),
            $this->plan(MultiTargetPropagationStatus::Completed, ['listing-3'], null, true),
        ]);

        self::assertSame(PublicProjectionDeliveryConsumptionResult::Consumed, $consumer->consume($this->message()));
        self::assertSame(['listing-1', 'listing-2', 'listing-3'], $updater->calls);
    }

    public function test_media_message_processes_all_listings_through_its_explicit_normalized_property(): void
    {
        $request = new MultiTargetPropagationRequest(MultiTargetPropagationSource::Media, '8a000000-0000-4000-8000-000000000001', self::PROPERTY);
        [$consumer, $updater] = $this->consumer(
            [new MultiTargetPropagationPlan($request, MultiTargetPropagationStatus::Completed, ['listing-1', 'listing-2'], null, true, PropertyListingsDiagnostic::None)],
            request: $request,
        );

        self::assertSame(PublicProjectionDeliveryConsumptionResult::Consumed, $consumer->consume($this->mediaMessage()));
        self::assertSame(['listing-1', 'listing-2'], $updater->calls);
    }

    public function test_all_already_applied_targets_return_already_consumed(): void
    {
        [$consumer] = $this->consumer(
            [$this->plan(MultiTargetPropagationStatus::Completed, ['listing-1', 'listing-2'], null, true)],
            ['listing-1' => PublicListingProjectionUpdateOutcome::AlreadyApplied, 'listing-2' => PublicListingProjectionUpdateOutcome::AlreadyApplied],
        );
        self::assertSame(PublicProjectionDeliveryConsumptionResult::AlreadyConsumed, $consumer->consume($this->message()));
    }

    public function test_redelivery_replays_applied_targets_idempotently_then_finishes_remaining_targets(): void
    {
        $pages = [$this->plan(MultiTargetPropagationStatus::Completed, ['listing-1', 'listing-2'], null, true)];
        [$first] = $this->consumer($pages, ['listing-1' => PublicListingProjectionUpdateOutcome::Applied, 'listing-2' => PublicListingProjectionUpdateOutcome::SourceUnavailable]);
        self::assertSame(PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness, $first->consume($this->message()));

        [$redelivery, $updater] = $this->consumer($pages, ['listing-1' => PublicListingProjectionUpdateOutcome::AlreadyApplied, 'listing-2' => PublicListingProjectionUpdateOutcome::Applied]);
        self::assertSame(PublicProjectionDeliveryConsumptionResult::Consumed, $redelivery->consume($this->message()));
        self::assertSame(['listing-1', 'listing-2'], $updater->calls);
    }

    public function test_processing_stops_immediately_on_intermediate_failure(): void
    {
        [$consumer, $updater] = $this->consumer(
            [$this->plan(MultiTargetPropagationStatus::Completed, ['listing-1', 'listing-2', 'listing-3'], null, true)],
            ['listing-1' => PublicListingProjectionUpdateOutcome::Applied, 'listing-2' => PublicListingProjectionUpdateOutcome::DivergentWatermark],
        );
        self::assertSame(PublicProjectionDeliveryConsumptionResult::DivergentPayload, $consumer->consume($this->message()));
        self::assertSame(['listing-1', 'listing-2'], $updater->calls);
    }

    public function test_corrupted_or_invalid_checkpoint_plan_is_permanent_failure(): void
    {
        foreach ([PropertyListingsDiagnostic::InvalidCheckpoint, PropertyListingsDiagnostic::CheckpointForAnotherProperty] as $diagnostic) {
            [$consumer, $updater] = $this->consumer([$this->plan(MultiTargetPropagationStatus::Corrupted, [], null, true, $diagnostic)]);
            self::assertSame(PublicProjectionDeliveryConsumptionResult::PermanentFailure, $consumer->consume($this->message()));
            self::assertSame([], $updater->calls);
        }
    }

    public function test_invalid_identity_and_media_ownership_diagnostics_are_permanent(): void
    {
        foreach ([
            [PublicProjectionSourceResolutionStatus::InvalidIdentity, PublicProjectionSourceDiagnostic::InvalidIdentity],
            [PublicProjectionSourceResolutionStatus::MediaOwnershipMissing, PublicProjectionSourceDiagnostic::MediaOwnershipMissing],
            [PublicProjectionSourceResolutionStatus::MediaOwnershipAmbiguous, PublicProjectionSourceDiagnostic::MediaOwnershipAmbiguous],
            [PublicProjectionSourceResolutionStatus::Corrupted, PublicProjectionSourceDiagnostic::Corrupted],
        ] as [$status, $diagnostic]) {
            $resolution = new PublicProjectionSourceResolution($status, diagnostic: $diagnostic);
            $updater = new RecordingUpdater([]);
            $consumer = new PublicProjectionUpdaterConsumer(new PublicProjectionSourceResolver(new PublicProjectionDeliveryEventCatalog, new FakePublicProjectionSourceLookup($resolution)), $updater);
            self::assertSame(PublicProjectionDeliveryConsumptionResult::PermanentFailure, $consumer->consume($this->message()));
        }
    }

    /** @param list<MultiTargetPropagationPlan> $plans
     * @param  array<string, PublicListingProjectionUpdateOutcome>  $outcomes
     * @return array{PublicProjectionUpdaterConsumer, RecordingUpdater}
     */
    private function consumer(array $plans, array $outcomes = [], ?MultiTargetPropagationRequest $request = null): array
    {
        $request ??= $this->request();
        $resolution = new PublicProjectionSourceResolution(PublicProjectionSourceResolutionStatus::MultiTargetResolved, multiTargetRequest: $request);
        $strategy = new QueuedStrategy($plans);
        $updater = new RecordingUpdater($outcomes);

        return [new PublicProjectionUpdaterConsumer(new PublicProjectionSourceResolver(new PublicProjectionDeliveryEventCatalog, new FakePublicProjectionSourceLookup($resolution)), $updater, $strategy, 2), $updater];
    }

    /** @param list<string> $ids */
    private function plan(MultiTargetPropagationStatus $status, array $ids, ?string $next, bool $completed, PropertyListingsDiagnostic $diagnostic = PropertyListingsDiagnostic::None): MultiTargetPropagationPlan
    {
        return new MultiTargetPropagationPlan($this->request(), $status, $ids, $next, $completed, $diagnostic);
    }

    private function request(): MultiTargetPropagationRequest
    {
        return new MultiTargetPropagationRequest(MultiTargetPropagationSource::Property, self::PROPERTY, self::PROPERTY);
    }

    private function message(): PublicProjectionDeliveryMessage
    {
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString('property.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('RealEstateCatalog'), PublicProjectionDeliveryAggregateType::fromString('Property'), PublicProjectionDeliveryAggregateId::fromString(self::PROPERTY), new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)), new DateTimeImmutable('2026-07-19T10:00:00+00:00'), new PublicProjectionDeliveryPropertyPayload(self::PROPERTY));

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable('2026-07-19T10:01:00+00:00'));
    }

    private function mediaMessage(): PublicProjectionDeliveryMessage
    {
        $mediaId = '8a000000-0000-4000-8000-000000000001';
        $fact = new PublicProjectionDeliveryPublishableFact(PublicProjectionDeliveryEventType::fromString('media.reconstruction.requested'), PublicProjectionDeliveryPayloadVersion::fromInt(1), PublicProjectionDeliverySourceModule::fromString('Media'), PublicProjectionDeliveryAggregateType::fromString('MediaCollection'), PublicProjectionDeliveryAggregateId::fromString($mediaId), new PublicProjectionDeliveryOrder(1, PublicProjectionDeliveryEventIndex::fromInt(1)), new DateTimeImmutable('2026-07-19T10:00:00+00:00'), new PublicProjectionDeliveryMediaPayload($mediaId));

        return (new PublicProjectionDeliveryCatalogMessageFactory(new PublicProjectionDeliveryEventCatalog))->create($fact, new DateTimeImmutable('2026-07-19T10:01:00+00:00'));
    }
}

final class QueuedStrategy implements MultiTargetPropagationStrategy
{
    /** @param list<MultiTargetPropagationPlan> $plans */
    public function __construct(private array $plans) {}

    public function plan(MultiTargetPropagationRequest $request, ?string $checkpoint, int $limit): MultiTargetPropagationPlan
    {
        return array_shift($this->plans) ?? throw new \LogicException('Missing plan.');
    }
}

final class RecordingUpdater implements PublicProjectionUpdateExecutor
{
    /** @var list<string> */
    public array $calls = [];

    /** @param array<string, PublicListingProjectionUpdateOutcome> $outcomes */
    public function __construct(private array $outcomes) {}

    public function update(string $listingId): PublicListingProjectionUpdateResult
    {
        $this->calls[] = $listingId;

        return new PublicListingProjectionUpdateResult($this->outcomes[$listingId] ?? PublicListingProjectionUpdateOutcome::Applied);
    }
}
