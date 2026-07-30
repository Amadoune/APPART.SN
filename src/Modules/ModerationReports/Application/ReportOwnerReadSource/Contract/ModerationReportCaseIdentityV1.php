<?php

namespace Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\Contract;

interface ModerationReportCaseIdentityV1
{
    public function caseId(string $reportId): string;
}
