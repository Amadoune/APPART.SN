<?php

namespace Appart\Modules\ModerationReports\Domain\Event;

use Appart\Modules\ModerationReports\Domain\Model\ReportValidationAssessment;
use Appart\Modules\ModerationReports\Domain\ValueObject\ListingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModeratorId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;
use DateTimeImmutable;

final readonly class ReportValidated extends AbstractModerationCaseEvent
{
    public function __construct(ModerationCaseId $caseId, ListingId $listingId, public ReportId $reportId, public ReportValidationAssessment $assessment, public ModerationRationale $rationale, public ModeratorId $validatedBy, DateTimeImmutable $at)
    {
        parent::__construct($caseId, $listingId, $at);
    }
}
