<?php

namespace Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries;

use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\OwnModerationReportViewV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceState;

final readonly class OwnModerationReportViewMapperV1
{
    public function map(string $reportId, ModerationCasePersistenceState $case): OwnModerationReportViewV1
    {
        return new OwnModerationReportViewV1($reportId, $case->status, $case->updatedAt);
    }
}
