<?php

namespace Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract;

use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceReadResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceState;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;

interface ModerationCaseStore
{
    public function read(string $caseId): ModerationCasePersistenceReadResult;

    public function save(
        ModerationCasePersistenceState $candidate,
        int $expectedVersion,
    ): ModerationPersistenceWriteResult;
}
