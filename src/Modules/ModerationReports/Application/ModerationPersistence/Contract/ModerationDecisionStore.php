<?php

namespace Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract;

use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationDecisionPersistenceReadResult;

interface ModerationDecisionStore
{
    public function read(string $caseId, string $decisionId): ModerationDecisionPersistenceReadResult;

    public function readCurrent(string $caseId): ModerationDecisionPersistenceReadResult;
}
