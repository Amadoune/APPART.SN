<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract;

use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadOwnModerationReportQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadOwnModerationReportResultV1;

interface ReadOwnModerationReportV1
{
    public function read(ReadOwnModerationReportQueryV1 $query): ReadOwnModerationReportResultV1;
}
