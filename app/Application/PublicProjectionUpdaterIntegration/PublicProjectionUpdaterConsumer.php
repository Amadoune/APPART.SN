<?php

namespace App\Application\PublicProjectionUpdaterIntegration;

use App\Application\MultiTargetDelivery\Contract\MultiTargetPropagationStrategy;
use App\Application\MultiTargetDelivery\MultiTargetPropagationStatus;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessage;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateOutcome;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionUpdateExecutor;

final readonly class PublicProjectionUpdaterConsumer implements PublicProjectionDeliveryConsumer
{
    public function __construct(
        private PublicProjectionSourceResolver $resolver,
        private PublicProjectionUpdateExecutor $updater,
        private ?MultiTargetPropagationStrategy $multiTargetStrategy = null,
        private int $multiTargetPageSize = 100,
    ) {}

    public function consume(PublicProjectionDeliveryMessage $message): PublicProjectionDeliveryConsumptionResult
    {
        $resolution = $this->resolver->resolve($message);

        return match ($resolution->status) {
            PublicProjectionSourceResolutionStatus::Resolved => $this->interpret($this->updater->update($resolution->listingId ?? '')->outcome),
            PublicProjectionSourceResolutionStatus::MultiTargetResolved => $this->consumeMultiple($resolution),
            PublicProjectionSourceResolutionStatus::SourceUnavailable,
            PublicProjectionSourceResolutionStatus::PromotionNotReady,
            PublicProjectionSourceResolutionStatus::WatermarkIncomplete,
            PublicProjectionSourceResolutionStatus::MissingPublicGeographyRevision,
            PublicProjectionSourceResolutionStatus::MissingPublicMediaRevision,
            PublicProjectionSourceResolutionStatus::MissingPublicGeographyAndMediaRevisions => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
            PublicProjectionSourceResolutionStatus::InvalidIdentity,
            PublicProjectionSourceResolutionStatus::Corrupted,
            PublicProjectionSourceResolutionStatus::MediaOwnershipMissing,
            PublicProjectionSourceResolutionStatus::MediaOwnershipAmbiguous => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
        };
    }

    private function consumeMultiple(PublicProjectionSourceResolution $resolution): PublicProjectionDeliveryConsumptionResult
    {
        if ($this->multiTargetStrategy === null || $resolution->multiTargetRequest === null || $this->multiTargetPageSize < 1) {
            return PublicProjectionDeliveryConsumptionResult::PermanentFailure;
        }
        $checkpoint = null;
        $seen = [];
        $anyApplied = false;
        do {
            $plan = $this->multiTargetStrategy->plan($resolution->multiTargetRequest, $checkpoint, $this->multiTargetPageSize);
            if (in_array($plan->status, [MultiTargetPropagationStatus::InvalidIdentity, MultiTargetPropagationStatus::Corrupted], true)) {
                return PublicProjectionDeliveryConsumptionResult::PermanentFailure;
            }
            if ($plan->status === MultiTargetPropagationStatus::NoTargets) {
                return PublicProjectionDeliveryConsumptionResult::Consumed;
            }
            foreach ($plan->listingIds as $listingId) {
                $outcome = $this->interpret($this->updater->update($listingId)->outcome);
                if (! in_array($outcome, [PublicProjectionDeliveryConsumptionResult::Consumed, PublicProjectionDeliveryConsumptionResult::AlreadyConsumed], true)) {
                    return $outcome;
                }
                $anyApplied = $anyApplied || $outcome === PublicProjectionDeliveryConsumptionResult::Consumed;
            }
            if ($plan->completed) {
                return $anyApplied ? PublicProjectionDeliveryConsumptionResult::Consumed : PublicProjectionDeliveryConsumptionResult::AlreadyConsumed;
            }
            $checkpoint = $plan->nextCheckpoint;
            if ($checkpoint === null || isset($seen[$checkpoint])) {
                return PublicProjectionDeliveryConsumptionResult::PermanentFailure;
            }
            $seen[$checkpoint] = true;
        } while (true);
    }

    private function interpret(PublicListingProjectionUpdateOutcome $outcome): PublicProjectionDeliveryConsumptionResult
    {
        return match ($outcome) {
            PublicListingProjectionUpdateOutcome::Applied => PublicProjectionDeliveryConsumptionResult::Consumed,
            PublicListingProjectionUpdateOutcome::AlreadyApplied => PublicProjectionDeliveryConsumptionResult::AlreadyConsumed,
            PublicListingProjectionUpdateOutcome::RejectedObsolete => PublicProjectionDeliveryConsumptionResult::RejectedObsolete,
            PublicListingProjectionUpdateOutcome::SourceUnavailable,
            PublicListingProjectionUpdateOutcome::ProjectionUnavailable,
            PublicListingProjectionUpdateOutcome::PromotionNotReady,
            PublicListingProjectionUpdateOutcome::IncompleteWatermark => PublicProjectionDeliveryConsumptionResult::BlockedBySourceReadiness,
            PublicListingProjectionUpdateOutcome::DivergentWatermark => PublicProjectionDeliveryConsumptionResult::DivergentPayload,
            PublicListingProjectionUpdateOutcome::CanonicalCollision,
            PublicListingProjectionUpdateOutcome::CanonicalReplacementRequired,
            PublicListingProjectionUpdateOutcome::HistoricalReservationConflict,
            PublicListingProjectionUpdateOutcome::GenerationMismatch => PublicProjectionDeliveryConsumptionResult::PermanentFailure,
        };
    }
}
