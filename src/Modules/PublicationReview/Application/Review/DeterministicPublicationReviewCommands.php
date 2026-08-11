<?php

namespace Appart\Modules\PublicationReview\Application\Review;

use Appart\Modules\ListingLifecycle\Application\PublicationGateway\Contract\ListingPublicationCommandGatewayV1;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueItem;
use Appart\Modules\PublicationReview\Application\Review\Contract\ApprovePublicationV1;
use Appart\Modules\PublicationReview\Application\Review\Contract\BeginPublicationReviewV1;
use Appart\Modules\PublicationReview\Application\Review\Contract\PublicationReviewCommandStore;
use DateTimeImmutable;

final readonly class DeterministicPublicationReviewCommands implements ApprovePublicationV1, BeginPublicationReviewV1
{
    public function __construct(
        private PublicationReviewCommandStore $store,
        private ListingPublicationCommandGatewayV1 $gateway,
    ) {}

    public function begin(string $queueItemId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt): PublicationReviewCommandResult
    {
        return $this->store->execute(
            'begin_review',
            $queueItemId,
            $commandId,
            $expectedVersion,
            $actor,
            $occurredAt,
            false,
            fn (PublicationReviewQueueItem $item) => $this->gateway->beginReview($item->listingId, $commandId, $item->submissionVersion, $actor, $occurredAt),
        );
    }

    public function approve(string $queueItemId, string $commandId, int $expectedVersion, string $actor, DateTimeImmutable $occurredAt): PublicationReviewCommandResult
    {
        return $this->store->execute(
            'approve_and_publish',
            $queueItemId,
            $commandId,
            $expectedVersion,
            $actor,
            $occurredAt,
            true,
            fn (PublicationReviewQueueItem $item) => $this->gateway->approveAndPublish($item->listingId, $commandId, $item->submissionVersion + 1, $actor, $occurredAt),
        );
    }
}
