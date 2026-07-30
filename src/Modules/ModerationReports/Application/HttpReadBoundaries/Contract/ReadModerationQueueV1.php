<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract;

use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationQueueQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationQueueResultV1;

interface ReadModerationQueueV1
{
    public function read(ReadModerationQueueQueryV1 $query): ReadModerationQueueResultV1;
}
