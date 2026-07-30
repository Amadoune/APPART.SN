<?php

namespace App\Application\ModerationOrchestration\Contract;

use DateTimeImmutable;

final readonly class ValidateModerationReportV1
{
    public function __construct(
        public string $intentId, public string $caseId, public string $reportId,
        public string $actorAccountId, public string $disposition, public string $reasonCode,
        public int $expectedVersion, public DateTimeImmutable $occurredAt,
        public string $policyVersion,
    ) {}
}
