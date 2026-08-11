<?php

namespace App\Application\PublicationReviewExperience;

use App\Application\PublicationReviewExperience\Contract\PublicationReviewExperienceV1;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\Contract\PublicationReviewAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\PublicationReviewAuthorizationStatus;
use Appart\Modules\IdentityAccess\Application\PublicationReviewAuthorization\PublicationReviewCapabilityV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\PublicationReview\Application\Projection\Contract\ProjectPublishedListingV1;
use Appart\Modules\PublicationReview\Application\Projection\ProjectPublishedListingStatus;
use Appart\Modules\PublicationReview\Application\Queue\Contract\ClaimPublicationReviewV1;
use Appart\Modules\PublicationReview\Application\Queue\Contract\PublicationReviewQueueReaderV1;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewClaimStatus;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueItem;
use Appart\Modules\PublicationReview\Application\Queue\PublicationReviewQueueReadStatus;
use Appart\Modules\PublicationReview\Application\Review\Contract\ApprovePublicationV1;
use Appart\Modules\PublicationReview\Application\Review\Contract\BeginPublicationReviewV1;
use Appart\Modules\PublicationReview\Application\Review\PublicationReviewCommandStatus;
use DateTimeImmutable;

final readonly class DeterministicPublicationReviewExperience implements PublicationReviewExperienceV1
{
    public function __construct(
        private PublicationReviewAuthorizationReaderV1 $authorization,
        private PublicationReviewQueueReaderV1 $queue,
        private ClaimPublicationReviewV1 $claim,
        private BeginPublicationReviewV1 $begin,
        private ApprovePublicationV1 $approve,
        private ProjectPublishedListingV1 $project,
    ) {}

    public function queue(AccountId $accountId, DateTimeImmutable $observedAt): PublicationReviewExperienceResult
    {
        if (($denied = $this->authorize($accountId, PublicationReviewCapabilityV1::ReadPublicationReviewQueue, $observedAt)) !== null) {
            return $denied;
        }
        $page = $this->queue->read(null, 100);

        return match ($page->status) {
            PublicationReviewQueueReadStatus::Available => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Available, ['items' => $page->items]),
            PublicationReviewQueueReadStatus::Empty => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Empty),
            default => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::DependencyUnavailable),
        };
    }

    public function candidate(AccountId $accountId, string $queueItemId, DateTimeImmutable $observedAt): PublicationReviewExperienceResult
    {
        $queue = $this->queue($accountId, $observedAt);
        if ($queue->status !== PublicationReviewExperienceStatus::Available) {
            return $queue;
        }
        $item = array_find($queue->data['items'], static fn (PublicationReviewQueueItem $item): bool => $item->queueItemId === $queueItemId);

        return $item instanceof PublicationReviewQueueItem
            ? new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Available, ['item' => $item])
            : new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::NotFound);
    }

    public function claim(AccountId $accountId, string $queueItemId, string $commandId, int $expectedVersion, DateTimeImmutable $occurredAt): PublicationReviewExperienceResult
    {
        if (($denied = $this->authorize($accountId, PublicationReviewCapabilityV1::ClaimPublicationReview, $occurredAt)) !== null) {
            return $denied;
        }
        $result = $this->claim->claim($commandId, $queueItemId, $accountId->value, $occurredAt, $expectedVersion);

        return match ($result->status) {
            PublicationReviewClaimStatus::Applied => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Applied, ['item' => $result->item]),
            PublicationReviewClaimStatus::AlreadyApplied => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::AlreadyApplied, ['item' => $result->item]),
            PublicationReviewClaimStatus::Missing => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::NotFound),
            PublicationReviewClaimStatus::DependencyUnavailable => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::DependencyUnavailable),
            default => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Conflict, ['item' => $result->item]),
        };
    }

    public function begin(AccountId $accountId, string $queueItemId, string $commandId, int $expectedVersion, DateTimeImmutable $occurredAt): PublicationReviewExperienceResult
    {
        if (($denied = $this->authorize($accountId, PublicationReviewCapabilityV1::BeginPublicationReview, $occurredAt)) !== null) {
            return $denied;
        }
        $result = $this->begin->begin($queueItemId, $commandId, $expectedVersion, $accountId->value, $occurredAt);

        return $this->command($result->status, $result->queueVersion);
    }

    public function approve(AccountId $accountId, string $queueItemId, string $listingId, int $submissionVersion, string $commandId, string $projectionCommandId, int $expectedVersion, DateTimeImmutable $occurredAt): PublicationReviewExperienceResult
    {
        if (($denied = $this->authorize($accountId, PublicationReviewCapabilityV1::ApprovePublication, $occurredAt)) !== null) {
            return $denied;
        }
        $approved = $this->approve->approve($queueItemId, $commandId, $expectedVersion, $accountId->value, $occurredAt);
        if (! in_array($approved->status, [PublicationReviewCommandStatus::Applied, PublicationReviewCommandStatus::AlreadyApplied], true)) {
            return $this->command($approved->status, $approved->queueVersion);
        }
        $projected = $this->project->project($listingId, $submissionVersion + 2, $projectionCommandId, $occurredAt);

        return match ($projected->status) {
            ProjectPublishedListingStatus::Applied => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Applied, ['listingId' => $listingId]),
            ProjectPublishedListingStatus::AlreadyApplied => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::AlreadyApplied, ['listingId' => $listingId]),
            ProjectPublishedListingStatus::NotReady => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::NotReady),
            ProjectPublishedListingStatus::Conflict => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Conflict),
            ProjectPublishedListingStatus::DependencyUnavailable => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::DependencyUnavailable),
        };
    }

    private function authorize(AccountId $accountId, PublicationReviewCapabilityV1 $capability, DateTimeImmutable $observedAt): ?PublicationReviewExperienceResult
    {
        return match ($this->authorization->authorize($accountId, $capability, $observedAt)->status) {
            PublicationReviewAuthorizationStatus::Allowed => null,
            PublicationReviewAuthorizationStatus::Denied => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Forbidden),
            PublicationReviewAuthorizationStatus::DependencyUnavailable => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::DependencyUnavailable),
        };
    }

    private function command(PublicationReviewCommandStatus $status, ?int $version): PublicationReviewExperienceResult
    {
        return match ($status) {
            PublicationReviewCommandStatus::Applied => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Applied, ['version' => $version]),
            PublicationReviewCommandStatus::AlreadyApplied => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::AlreadyApplied, ['version' => $version]),
            PublicationReviewCommandStatus::Missing => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::NotFound),
            PublicationReviewCommandStatus::DependencyUnavailable => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::DependencyUnavailable),
            default => new PublicationReviewExperienceResult(PublicationReviewExperienceStatus::Conflict, ['version' => $version]),
        };
    }
}
