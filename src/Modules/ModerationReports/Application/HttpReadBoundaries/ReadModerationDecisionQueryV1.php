<?php

namespace Appart\Modules\ModerationReports\Application\HttpReadBoundaries;

use DateTimeImmutable;

final readonly class ReadModerationDecisionQueryV1
{
    public function __construct(
        public string $caseId,
        public ?string $decisionId,
        public string $actorAccountId,
        public DateTimeImmutable $observedAt,
        public ModerationDecisionPurposeV1 $purpose,
    ) {}
}
