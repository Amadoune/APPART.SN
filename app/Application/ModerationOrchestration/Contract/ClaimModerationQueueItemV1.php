<?php

namespace App\Application\ModerationOrchestration\Contract;

use DateTimeImmutable;

final readonly class ClaimModerationQueueItemV1
{
    public function __construct(
        public string $intentId, public string $queueItemId, public string $actorAccountId,
        public string $leaseId, public DateTimeImmutable $leaseExpiresAt,
        public DateTimeImmutable $occurredAt, public string $policyVersion,
    ) {}
}
