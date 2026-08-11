<?php

namespace Appart\Modules\PublicationReview\Application\Review;

final readonly class PublicationReviewCommandResult
{
    public function __construct(
        public PublicationReviewCommandStatus $status,
        public ?int $queueVersion = null,
    ) {}
}
