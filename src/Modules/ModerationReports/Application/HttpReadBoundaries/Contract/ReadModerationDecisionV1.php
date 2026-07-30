<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract;

use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationDecisionQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationDecisionResultV1;

interface ReadModerationDecisionV1
{
    public function read(ReadModerationDecisionQueryV1 $query): ReadModerationDecisionResultV1;
}
