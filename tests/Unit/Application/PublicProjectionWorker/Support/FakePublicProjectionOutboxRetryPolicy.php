<?php

namespace Tests\Unit\Application\PublicProjectionWorker\Support;

use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxRetryPolicy;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxAttemptCount;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryBackoff;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryClassification;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;

final readonly class FakePublicProjectionOutboxRetryPolicy implements PublicProjectionOutboxRetryPolicy
{
    public function __construct(private int $maximumAttempts = 3) {}

    public function decide(PublicProjectionOutboxRetryClassification $classification, PublicProjectionOutboxAttemptCount $attempts): PublicProjectionOutboxRetryDecision
    {
        return new PublicProjectionOutboxRetryDecision($classification, new PublicProjectionOutboxRetryBackoff(0), $attempts->value < $this->maximumAttempts);
    }
}
