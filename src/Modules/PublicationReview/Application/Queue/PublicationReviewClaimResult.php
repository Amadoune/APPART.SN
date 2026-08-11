<?php

namespace Appart\Modules\PublicationReview\Application\Queue;

final readonly class PublicationReviewClaimResult
{
    public function __construct(
        public PublicationReviewClaimStatus $status,
        public ?PublicationReviewQueueItem $item = null,
    ) {}
}
