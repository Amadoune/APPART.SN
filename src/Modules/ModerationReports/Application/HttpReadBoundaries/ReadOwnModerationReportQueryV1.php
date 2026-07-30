<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

final readonly class ReadOwnModerationReportQueryV1
{
    public function __construct(
        public string $reportId,
        public string $actorAccountId,
    ) {}
}
