<?php

namespace Appart\Modules\PublicationReview\Application\Queue;

use DateTimeImmutable;

final readonly class PublicationReviewQueueItem
{
    public function __construct(
        public string $queueItemId,
        public string $sourceEventId,
        public string $listingId,
        public int $submissionVersion,
        public DateTimeImmutable $submittedAt,
        public PublicationReviewQueueItemState $state,
        public int $version,
        public ?string $claimedBy = null,
        public ?DateTimeImmutable $claimedAt = null,
    ) {}
}
