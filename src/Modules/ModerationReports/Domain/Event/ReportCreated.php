<?php

namespace Appart\Modules\ModerationReports\Domain\Event;

use Appart\Modules\ModerationReports\Domain\ValueObject\ListingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReporterId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportReason;
use DateTimeImmutable;

final readonly class ReportCreated extends AbstractModerationCaseEvent
{
    public function __construct(ModerationCaseId $caseId, ListingId $listingId, public ReportId $reportId, public ReporterId $reporterId, public ReportReason $reason, DateTimeImmutable $at)
    {
        parent::__construct($caseId, $listingId, $at);
    }
}
