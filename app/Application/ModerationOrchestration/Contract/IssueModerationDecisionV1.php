<?php

namespace App\Application\ModerationOrchestration\Contract;

use DateTimeImmutable;

final readonly class IssueModerationDecisionV1
{
    /** @param list<string> $findingIds */
    public function __construct(
        public string $intentId, public string $caseId, public string $decisionId,
        public string $actorAccountId, public array $findingIds, public string $disposition,
        public string $targetAction, public ?string $supersededDecisionId,
        public int $expectedVersion, public DateTimeImmutable $occurredAt,
        public string $policyVersion,
    ) {}
}
