<?php

namespace Appart\Modules\ModerationReports\Application\QueueOwnerReadSource;

final readonly class ModerationQueueReadPageV1
{
    /** @param list<ModerationQueueReadItemV1> $items */
    public function __construct(
        public array $items,
        public ?string $nextCursor,
    ) {}
}
