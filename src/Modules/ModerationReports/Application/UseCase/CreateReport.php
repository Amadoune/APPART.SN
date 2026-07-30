<?php

namespace Appart\Modules\ModerationReports\Application\UseCase;

use Appart\Modules\ModerationReports\Application\Contract\ListingCatalog;
use Appart\Modules\ModerationReports\Application\Contract\ModerationCaseRegistry;
use Appart\Modules\ModerationReports\Domain\Exception\ListingUnavailable;
use Appart\Modules\ModerationReports\Domain\Model\ModerationCase;
use Appart\Modules\ModerationReports\Domain\ValueObject\ListingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReporterId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportReason;
use DateTimeImmutable;

final readonly class CreateReport
{
    public function __construct(private ModerationCaseRegistry $cases, private ListingCatalog $listings) {}

    public function execute(ModerationCaseId $caseId, ListingId $listingId, ReportId $reportId, ReporterId $reporterId, ReportReason $reason, DateTimeImmutable $at): ModerationCase
    {
        if (! $this->listings->eligibilityOf($listingId)->permitsModeration()) {
            throw new ListingUnavailable;
        }$case = $this->cases->find($caseId);
        if ($case === null) {
            $case = ModerationCase::open($caseId, $listingId, $reportId, $reporterId, $reason, $at);
            $this->cases->addWithReportReservation($case, $reportId);

            return $case;
        }$version = $case->version();
        $case->addReport($listingId, $reportId, $reporterId, $reason, $at);
        $this->cases->saveWithReportReservation($case, $reportId, $version);

        return $case;
    }
}
