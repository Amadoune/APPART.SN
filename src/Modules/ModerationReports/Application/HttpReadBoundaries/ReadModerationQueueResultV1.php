<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadPageV1;

final readonly class ReadModerationQueueResultV1
{
    private function __construct(
        public ReadStatusV1 $status,
        public ?ModerationQueueReadPageV1 $page,
    ) {}

    public static function available(ModerationQueueReadPageV1 $page): self
    {
        return new self(ReadStatusV1::Available, $page);
    }

    public static function status(ReadStatusV1 $status): self
    {
        return new self($status, null);
    }
}
