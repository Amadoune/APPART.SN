<?php

namespace App\Application\ModerationRuntime\Contract;

use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueClaimResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueItemState;
use DateTimeImmutable;

interface ModerationQueueRuntimeV1
{
    public function project(ModerationQueueItemState $item): ModerationPersistenceWriteResult;

    public function claim(
        string $queueItemId,
        string $leaseId,
        string $claimOwnerId,
        DateTimeImmutable $leaseExpiresAt,
        DateTimeImmutable $claimedAt,
        ?string $intentId = null,
        ?string $intentChecksum = null,
    ): ModerationQueueClaimResult;

    public function read(string $queueItemId): ?ModerationQueueItemState;

    public function checkpoint(
        string $projectionName,
        int $checkpoint,
        DateTimeImmutable $updatedAt,
    ): ModerationPersistenceWriteResult;
}
