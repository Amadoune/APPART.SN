<?php

namespace Appart\Modules\ModerationReports\Application\ModerationPersistence;

use DateTimeImmutable;

final readonly class ModerationQueueItemState
{
    public function __construct(
        public string $queueItemId,
        public string $caseId,
        public int $priority,
        public string $category,
        public string $state,
        public ?string $leaseId,
        public ?string $claimOwnerId,
        public ?DateTimeImmutable $leaseExpiresAt,
        public int $sourceVersion,
        public DateTimeImmutable $updatedAt,
    ) {}
}
