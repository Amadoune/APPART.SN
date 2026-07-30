<?php

namespace App\Application\ModerationOrchestration\Contract;

use DateTimeImmutable;

final readonly class RecordModerationFindingV1
{
    /**
     * @param  list<string>  $reportIds
     * @param  list<string>  $evidenceReferences
     */
    public function __construct(
        public string $intentId, public string $caseId, public string $findingId,
        public string $actorAccountId, public array $reportIds, public string $findingCode,
        public array $evidenceReferences, public int $expectedVersion,
        public DateTimeImmutable $occurredAt, public string $policyVersion,
    ) {}
}
