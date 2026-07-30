<?php

namespace App\Application\ModerationOrchestration\Contract;

use DateTimeImmutable;

final readonly class CloseModerationCaseV1
{
    public function __construct(
        public string $intentId, public string $caseId, public string $actorAccountId,
        public string $closureCode, public int $expectedVersion,
        public DateTimeImmutable $occurredAt, public string $policyVersion,
    ) {}
}
