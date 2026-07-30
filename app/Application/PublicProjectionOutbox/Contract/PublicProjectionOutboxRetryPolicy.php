<?php

namespace App\Application\PublicProjectionOutbox\Contract;

use App\Application\PublicProjectionOutbox\PublicProjectionOutboxAttemptCount;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryClassification;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;

interface PublicProjectionOutboxRetryPolicy
{
    public function decide(PublicProjectionOutboxRetryClassification $classification, PublicProjectionOutboxAttemptCount $attempts): PublicProjectionOutboxRetryDecision;
}
