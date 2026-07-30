<?php

namespace App\Application\ModerationRuntime;

use App\Application\ModerationRuntime\Contract\ModerationQueueRuntimeV1;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeAvailabilityPolicy;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeReport;
use App\Application\ModerationRuntime\Contract\ModerationRuntimeV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationCaseStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationDecisionStore;

final readonly class DeterministicModerationRuntimeV1 implements ModerationRuntimeV1
{
    public function __construct(
        private ModerationCaseStore $cases,
        private ModerationDecisionStore $decisions,
        private ModerationQueueRuntimeV1 $queue,
        private ModerationRuntimeAvailabilityPolicy $availability,
    ) {}

    public function cases(): ModerationCaseStore
    {
        return $this->cases;
    }

    public function decisions(): ModerationDecisionStore
    {
        return $this->decisions;
    }

    public function queue(): ModerationQueueRuntimeV1
    {
        return $this->queue;
    }

    public function inspect(): ModerationRuntimeReport
    {
        return $this->availability->inspect();
    }
}
