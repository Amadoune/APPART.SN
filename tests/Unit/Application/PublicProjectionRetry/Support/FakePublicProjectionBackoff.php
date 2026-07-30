<?php

namespace Tests\Unit\Application\PublicProjectionRetry\Support;

use App\Application\PublicProjectionOutbox\PublicProjectionOutboxAttemptCount;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryBackoff;
use App\Application\PublicProjectionRetry\Contract\PublicProjectionRetryBackoffStrategy;

final readonly class FakePublicProjectionBackoff implements PublicProjectionRetryBackoffStrategy
{
    public function __construct(private int $seconds) {}

    public function forAttempt(PublicProjectionOutboxAttemptCount $attempt): PublicProjectionOutboxRetryBackoff
    {
        return new PublicProjectionOutboxRetryBackoff($this->seconds);
    }
}
