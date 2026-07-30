<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract;

use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationCaseQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationCaseResultV1;

interface ReadModerationCaseV1
{
    public function read(ReadModerationCaseQueryV1 $query): ReadModerationCaseResultV1;
}
