<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

use DateTimeImmutable;

final readonly class OwnModerationReportViewV1
{
    public function __construct(
        public string $reportId,
        public string $status,
        public DateTimeImmutable $updatedAt,
    ) {}
}
