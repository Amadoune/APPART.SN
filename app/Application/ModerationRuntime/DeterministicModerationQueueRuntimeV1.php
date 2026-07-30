<?php

namespace App\Application\ModerationRuntime;

use App\Application\ModerationRuntime\Contract\ModerationQueueRuntimeV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationQueueStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueClaimResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueItemState;
use DateTimeImmutable;

final readonly class DeterministicModerationQueueRuntimeV1 implements ModerationQueueRuntimeV1
{
    public function __construct(private ModerationQueueStore $queue) {}

    public function project(ModerationQueueItemState $item): ModerationPersistenceWriteResult
    {
        return $this->queue->project($item);
    }

    public function claim(
        string $queueItemId,
        string $leaseId,
        string $claimOwnerId,
        DateTimeImmutable $leaseExpiresAt,
        DateTimeImmutable $claimedAt,
        ?string $intentId = null,
        ?string $intentChecksum = null,
    ): ModerationQueueClaimResult {
        return $this->queue->claim(
            $queueItemId,
            $leaseId,
            $claimOwnerId,
            $leaseExpiresAt,
            $claimedAt,
            $intentId,
            $intentChecksum,
        );
    }

    public function read(string $queueItemId): ?ModerationQueueItemState
    {
        return $this->queue->read($queueItemId);
    }

    public function checkpoint(
        string $projectionName,
        int $checkpoint,
        DateTimeImmutable $updatedAt,
    ): ModerationPersistenceWriteResult {
        return $this->queue->checkpoint($projectionName, $checkpoint, $updatedAt);
    }
}
