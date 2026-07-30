<?php

namespace Appart\Modules\ModerationReports\Domain\Event;

use Appart\Modules\ModerationReports\Domain\ValueObject\FindingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingSeverity;
use Appart\Modules\ModerationReports\Domain\ValueObject\ListingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;
use DateTimeImmutable;

final readonly class FindingRecorded extends AbstractModerationCaseEvent
{
    /** @param non-empty-list<ReportId> $reportIds */
    public function __construct(ModerationCaseId $caseId, ListingId $listingId, public FindingId $findingId, public array $reportIds, public FindingSeverity $severity, public ModerationRationale $rationale, public ModeratorId $recordedBy, DateTimeImmutable $at)
    {
        parent::__construct($caseId, $listingId, $at);
    }
}
