<?php

namespace Appart\Modules\PublicationReview\Application\Queue;

final readonly class PublicationReviewQueuePage
{
    /** @param list<PublicationReviewQueueItem> $items */
    public function __construct(
        public PublicationReviewQueueReadStatus $status,
        public array $items = [],
        public ?string $nextCursor = null,
    ) {}
}
