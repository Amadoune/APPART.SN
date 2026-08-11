<?php

namespace App\Application\PublicationReviewProjectionActivation;

use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdateOutcome;
use App\Application\PublicProjectionUpdater\PublicListingProjectionUpdater;
use Appart\Modules\PublicationReview\Application\Projection\Contract\PublicListingProjectionActivation;
use Appart\Modules\PublicationReview\Application\Projection\ProjectionActivationStatus;

final readonly class PublicListingProjectionActivationAdapter implements PublicListingProjectionActivation
{
    public function __construct(private PublicListingProjectionUpdater $updater) {}

    public function activate(string $listingId): ProjectionActivationStatus
    {
        return match ($this->updater->update($listingId)->outcome) {
            PublicListingProjectionUpdateOutcome::Applied => ProjectionActivationStatus::Applied,
            PublicListingProjectionUpdateOutcome::AlreadyApplied => ProjectionActivationStatus::AlreadyApplied,
            PublicListingProjectionUpdateOutcome::SourceUnavailable,
            PublicListingProjectionUpdateOutcome::ProjectionUnavailable,
            PublicListingProjectionUpdateOutcome::PromotionNotReady => ProjectionActivationStatus::NotReady,
            PublicListingProjectionUpdateOutcome::RejectedObsolete,
            PublicListingProjectionUpdateOutcome::DivergentWatermark,
            PublicListingProjectionUpdateOutcome::IncompleteWatermark,
            PublicListingProjectionUpdateOutcome::CanonicalCollision,
            PublicListingProjectionUpdateOutcome::CanonicalReplacementRequired,
            PublicListingProjectionUpdateOutcome::HistoricalReservationConflict,
            PublicListingProjectionUpdateOutcome::GenerationMismatch => ProjectionActivationStatus::Conflict,
        };
    }
}
