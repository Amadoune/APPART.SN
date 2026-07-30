<?php

namespace App\Application\PublicProjectionRetry\Contract;

use App\Application\PublicProjectionOutbox\PublicProjectionOutboxAttemptCount;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryBackoff;

interface PublicProjectionRetryBackoffStrategy
{
    public function forAttempt(PublicProjectionOutboxAttemptCount $attempt): PublicProjectionOutboxRetryBackoff;
}
