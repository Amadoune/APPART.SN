<?php

namespace App\Application\ModerationRuntime\Contract;

use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationCaseStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationDecisionStore;

interface ModerationRuntimeV1
{
    public function cases(): ModerationCaseStore;

    public function decisions(): ModerationDecisionStore;

    public function queue(): ModerationQueueRuntimeV1;

    public function inspect(): ModerationRuntimeReport;
}
