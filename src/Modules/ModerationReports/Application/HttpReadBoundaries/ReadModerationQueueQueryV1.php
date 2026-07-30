<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadFilterV1;
use DateTimeImmutable;

final readonly class ReadModerationQueueQueryV1
{
    public function __construct(
        public string $actorAccountId,
        public DateTimeImmutable $observedAt,
        public ModerationQueueReadFilterV1 $filter,
        public ?string $cursor,
        public int $limit,
    ) {}
}
