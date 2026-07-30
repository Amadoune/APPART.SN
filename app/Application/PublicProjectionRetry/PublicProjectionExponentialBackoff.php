<?php

namespace App\Application\PublicProjectionRetry;

use App\Application\PublicProjectionOutbox\PublicProjectionOutboxAttemptCount;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryBackoff;
use App\Application\PublicProjectionRetry\Contract\PublicProjectionRetryBackoffStrategy;
use InvalidArgumentException;

final readonly class PublicProjectionExponentialBackoff implements PublicProjectionRetryBackoffStrategy
{
    public function __construct(private int $initialSeconds, private int $maximumSeconds)
    {
        if ($initialSeconds < 0 || $maximumSeconds < $initialSeconds) {
            throw new InvalidArgumentException('Invalid deterministic exponential backoff bounds.');
        }
    }

    public function forAttempt(PublicProjectionOutboxAttemptCount $attempt): PublicProjectionOutboxRetryBackoff
    {
        $multiplier = 2 ** max(0, $attempt->value - 1);

        return new PublicProjectionOutboxRetryBackoff((int) min($this->maximumSeconds, $this->initialSeconds * $multiplier));
    }
}
