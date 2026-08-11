<?php

namespace Appart\Modules\PublicationReview\Application\Review\Contract;

use Appart\Modules\ListingLifecycle\Application\PublicationGateway\ListingPublicationCommandResultV1;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueItem;
use Appart\Modules\PublicationReview\Application\Review\PublicationReviewCommandResult;
use DateTimeImmutable;

interface PublicationReviewCommandStore
{
    /** @param callable(PublicationReviewQueueItem): ListingPublicationCommandResultV1 $gateway */
    public function execute(
        string $operation,
        string $queueItemId,
        string $commandId,
        int $expectedVersion,
        string $actor,
        DateTimeImmutable $occurredAt,
        bool $complete,
        callable $gateway,
    ): PublicationReviewCommandResult;
}
