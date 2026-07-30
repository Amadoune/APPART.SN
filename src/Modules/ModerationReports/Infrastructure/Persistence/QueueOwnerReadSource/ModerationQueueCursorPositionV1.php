<?php

namespace Appart\Modules\ModerationReports\Infrastructure\Persistence\QueueOwnerReadSource;

use DateTimeImmutable;

final readonly class ModerationQueueCursorPositionV1
{
    public function __construct(
        public int $priority,
        public DateTimeImmutable $updatedAt,
        public string $queueItemId,
    ) {}
}
