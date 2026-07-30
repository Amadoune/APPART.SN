<?php

namespace App\Application\PublicProjectionOutbox;

use InvalidArgumentException;

final readonly class PublicProjectionOutboxRetryBackoff
{
    public function __construct(public int $delaySeconds)
    {
        if ($delaySeconds < 0) {
            throw new InvalidArgumentException('Invalid Public Projection Outbox retry backoff.');
        }
    }
}
