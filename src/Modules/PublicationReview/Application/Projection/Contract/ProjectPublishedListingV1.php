<?php

namespace Appart\Modules\PublicationReview\Application\Projection\Contract;

use Appart\Modules\PublicationReview\Application\Projection\ProjectPublishedListingResult;
use DateTimeImmutable;

interface ProjectPublishedListingV1
{
    public function project(string $listingId, int $publicationVersion, string $commandId, DateTimeImmutable $occurredAt): ProjectPublishedListingResult;
}
