<?php

namespace Appart\Modules\PublicationReview\Application\Review\Contract;

use Appart\Modules\PublicationReview\Application\Review\PublicationReviewCommandResult;
use DateTimeImmutable;

interface BeginPublicationReviewV1
{
    public function begin(string $queueItemId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt): PublicationReviewCommandResult;
}
