<?php

namespace App\Application\PublicationReviewExperience\Contract;

use App\Application\PublicationReviewExperience\PublicationReviewExperienceResult;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;

interface PublicationReviewExperienceV1
{
    public function queue(AccountId $accountId, DateTimeImmutable $observedAt): PublicationReviewExperienceResult;

    public function candidate(AccountId $accountId, string $queueItemId, DateTimeImmutable $observedAt): PublicationReviewExperienceResult;

    public function claim(AccountId $accountId, string $queueItemId, string $commandId, int $expectedVersion, DateTimeImmutable $occurredAt): PublicationReviewExperienceResult;

    public function begin(AccountId $accountId, string $queueItemId, string $commandId, int $expectedVersion, DateTimeImmutable $occurredAt): PublicationReviewExperienceResult;

    public function approve(AccountId $accountId, string $queueItemId, string $listingId, int $submissionVersion, string $commandId, string $projectionCommandId, int $expectedVersion, DateTimeImmutable $occurredAt): PublicationReviewExperienceResult;
}
