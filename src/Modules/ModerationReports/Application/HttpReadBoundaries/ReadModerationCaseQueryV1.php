<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

use DateTimeImmutable;

final readonly class ReadModerationCaseQueryV1
{
    public function __construct(
        public string $caseId,
        public string $actorAccountId,
        public DateTimeImmutable $observedAt,
        public ModerationCaseViewLevelV1 $viewLevel,
    ) {}
}
