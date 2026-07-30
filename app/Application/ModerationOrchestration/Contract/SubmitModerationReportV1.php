<?php

namespace App\Application\ModerationOrchestration\Contract;

use DateTimeImmutable;

final readonly class SubmitModerationReportV1
{
    public function __construct(
        public string $intentId, public string $reportId, public string $actorAccountId,
        public string $targetType, public string $targetId, public string $category,
        public string $statementReference, public DateTimeImmutable $occurredAt,
        public string $policyVersion,
    ) {}
}
