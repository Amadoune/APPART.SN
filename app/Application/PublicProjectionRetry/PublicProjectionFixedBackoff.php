<?php

namespace App\Application\PublicProjectionRetry;

use App\Application\PublicProjectionOutbox\PublicProjectionOutboxAttemptCount;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryBackoff;
use App\Application\PublicProjectionRetry\Contract\PublicProjectionRetryBackoffStrategy;

final readonly class PublicProjectionFixedBackoff implements PublicProjectionRetryBackoffStrategy
{
    public function __construct(private int $delaySeconds) {}

    public function forAttempt(PublicProjectionOutboxAttemptCount $attempt): PublicProjectionOutboxRetryBackoff
    {
        return new PublicProjectionOutboxRetryBackoff($this->delaySeconds);
    }
}
