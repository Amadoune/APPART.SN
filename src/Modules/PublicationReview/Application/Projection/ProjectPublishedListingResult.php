<?php

namespace Appart\Modules\PublicationReview\Application\Projection;

final readonly class ProjectPublishedListingResult
{
    public function __construct(
        public ProjectPublishedListingStatus $status,
        public ?int $queueVersion = null,
    ) {}
}
