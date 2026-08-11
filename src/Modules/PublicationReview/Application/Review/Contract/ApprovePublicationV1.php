<?php

namespace Appart\Modules\PublicationReview\Application\Review\Contract;

use Appart\Modules\PublicationReview\Application\Review\PublicationReviewCommandResult;
use DateTimeImmutable;

interface ApprovePublicationV1
{
    public function approve(string $queueItemId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt): PublicationReviewCommandResult;
}
