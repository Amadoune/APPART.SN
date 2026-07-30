<?php

namespace Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\Contract;

use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadFilterV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadResultV1;

interface ModerationQueueOwnerReadSourceV1
{
    public function read(
        ModerationQueueReadFilterV1 $filter,
        ?string $cursor,
        int $limit,
    ): ModerationQueueReadResultV1;
}
