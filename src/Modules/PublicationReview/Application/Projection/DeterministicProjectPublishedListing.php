<?php

namespace Appart\Modules\PublicationReview\Application\Projection;

use Appart\Modules\PublicationReview\Application\Projection\Contract\ProjectPublishedListingV1;
use Appart\Modules\PublicationReview\Application\Projection\Contract\PublicationReviewProjectionStore;
use Appart\Modules\PublicationReview\Application\Projection\Contract\PublicListingProjectionActivation;
use DateTimeImmutable;

final readonly class DeterministicProjectPublishedListing implements ProjectPublishedListingV1
{
    public function __construct(
        private PublicationReviewProjectionStore $store,
        private PublicListingProjectionActivation $projection,
    ) {}

    public function project(string $listingId, int $publicationVersion, string $commandId, DateTimeImmutable $occurredAt): ProjectPublishedListingResult
    {
        return $this->store->activate(
            $listingId,
            $publicationVersion,
            $commandId,
            $occurredAt,
            fn (): ProjectionActivationStatus => $this->projection->activate($listingId),
        );
    }
}
