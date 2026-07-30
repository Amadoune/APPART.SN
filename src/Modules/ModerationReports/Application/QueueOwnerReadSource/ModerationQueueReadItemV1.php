<?php

namespace Appart\Modules\ModerationReports\Application\QueueOwnerReadSource;

use DateTimeImmutable;

final readonly class ModerationQueueReadItemV1
{
    public function __construct(
        public string $queueItemId,
        public string $caseId,
        public int $priority,
        public string $category,
        public ModerationQueueReadStateV1 $state,
        public ?DateTimeImmutable $leaseExpiresAt,
        public int $sourceVersion,
        public DateTimeImmutable $updatedAt,
    ) {}
}
