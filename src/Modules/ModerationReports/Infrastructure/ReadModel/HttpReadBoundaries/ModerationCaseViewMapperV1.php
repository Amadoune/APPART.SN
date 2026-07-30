<?php

namespace Appart\Modules\ModerationReports\Infrastructure\ReadModel\HttpReadBoundaries;

use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ModerationCaseViewV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceState;

final readonly class ModerationCaseViewMapperV1
{
    public function map(ModerationCasePersistenceState $case): ModerationCaseViewV1
    {
        return new ModerationCaseViewV1(
            $case->caseId,
            $case->targetType,
            $case->targetId,
            $case->status,
            $case->currentDecisionId,
            $case->version,
            $case->updatedAt,
        );
    }
}
