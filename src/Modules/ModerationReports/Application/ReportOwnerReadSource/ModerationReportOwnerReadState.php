<?php

namespace Appart\Modules\ModerationReports\Application\ReportOwnerReadSource;

final readonly class ModerationReportOwnerReadState
{
    public function __construct(
        public string $caseId,
        public string $reportId,
        public string $reporterAccountId,
    ) {}
}
