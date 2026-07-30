<?php

namespace App\Application\ModerationReportOwnerReadSource;

use App\Application\ModerationOrchestration\CanonicalModerationCommand;
use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\Contract\ModerationReportCaseIdentityV1;

final class CanonicalModerationReportCaseIdentityV1 implements ModerationReportCaseIdentityV1
{
    public function caseId(string $reportId): string
    {
        return CanonicalModerationCommand::deterministicUuid('moderation-case-v1', $reportId);
    }
}
