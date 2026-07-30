<?php

namespace Appart\Modules\ModerationReports\Application\UseCase;

use Appart\Modules\ModerationReports\Domain\ValueObject\FindingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingSeverity;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;
use DateTimeImmutable;

final readonly class RecordFinding extends ModerationCaseUseCase
{
    /** @param list<ReportId> $reportIds */
    public function execute(ModerationCaseId $caseId, FindingId $id, array $reportIds, FindingSeverity $severity, ModerationRationale $rationale, ModeratorId $moderator, DateTimeImmutable $at): void
    {
        [$case,$version] = $this->load($caseId);
        $case->recordFinding($id, $reportIds, $severity, $rationale, $moderator, $at);
        $this->cases->saveWithFindingReservation($case, $id, $version);
    }
}
