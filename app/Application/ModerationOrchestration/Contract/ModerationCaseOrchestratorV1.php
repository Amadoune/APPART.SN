<?php

namespace App\Application\ModerationOrchestration\Contract;

interface ModerationCaseOrchestratorV1
{
    public function submit(SubmitModerationReportV1 $command): ModerationCommandResult;

    public function validate(ValidateModerationReportV1 $command): ModerationCommandResult;

    public function recordFinding(RecordModerationFindingV1 $command): ModerationCommandResult;

    public function issueDecision(IssueModerationDecisionV1 $command): ModerationCommandResult;

    public function close(CloseModerationCaseV1 $command): ModerationCommandResult;

    public function claim(ClaimModerationQueueItemV1 $command): ModerationCommandResult;
}
