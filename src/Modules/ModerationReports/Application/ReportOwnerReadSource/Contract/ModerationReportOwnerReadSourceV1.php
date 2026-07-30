<?php

namespace Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\Contract;

use Appart\Modules\ModerationReports\Application\ReportOwnerReadSource\ModerationReportOwnerReadResult;

interface ModerationReportOwnerReadSourceV1
{
    public function resolve(string $reportId): ModerationReportOwnerReadResult;
}
