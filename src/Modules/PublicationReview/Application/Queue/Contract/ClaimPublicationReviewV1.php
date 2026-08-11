<?php

namespace Appart\Modules\PublicationReview\Application\Queue\Contract;

use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewClaimResult;
use DateTimeImmutable;

interface ClaimPublicationReviewV1
{
    public function claim(
        string $commandId,
        string $queueItemId,
        string $actor,
        DateTimeImmutable $occurredAt,
        int $expectedVersion,
    ): PublicationReviewClaimResult;
}
