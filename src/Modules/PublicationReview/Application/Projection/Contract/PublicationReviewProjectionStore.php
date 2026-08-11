<?php

namespace Appart\Modules\PublicationReview\Application\Projection\Contract;

use Appart\Modules\PublicationReview\Application\Projection\ProjectionActivationStatus;
use Appart\Modules\PublicationReview\Application\Projection\ProjectPublishedListingResult;
use DateTimeImmutable;

interface PublicationReviewProjectionStore
{
    /** @param callable(): ProjectionActivationStatus $activation */
    public function activate(string $listingId, int $publicationVersion, string $commandId, DateTimeImmutable $occurredAt, callable $activation): ProjectPublishedListingResult;
}
