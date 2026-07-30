<?php

namespace App\Application\PublicProjectionRetry;

use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxRetryPolicy;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxAttemptCount;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryBackoff;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryClassification;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;
use App\Application\PublicProjectionRetry\Contract\PublicProjectionRetryBackoffStrategy;
use InvalidArgumentException;

final readonly class PublicProjectionDeterministicRetryPolicy implements PublicProjectionOutboxRetryPolicy
{
    public function __construct(private int $maximumAttempts, private PublicProjectionRetryBackoffStrategy $backoff)
    {
        if ($maximumAttempts < 1) {
            throw new InvalidArgumentException('Maximum attempts must be positive.');
        }
    }

    public function decide(PublicProjectionOutboxRetryClassification $classification, PublicProjectionOutboxAttemptCount $attempts): PublicProjectionOutboxRetryDecision
    {
        return $this->schedule($classification, $attempts)->decision;
    }

    public function schedule(PublicProjectionOutboxRetryClassification $classification, PublicProjectionOutboxAttemptCount $attempts): PublicProjectionRetrySchedule
    {
        if (in_array($classification, [PublicProjectionOutboxRetryClassification::SourceNotReady, PublicProjectionOutboxRetryClassification::SequenceGap], true)) {
            return new PublicProjectionRetrySchedule(new PublicProjectionOutboxRetryDecision($classification, new PublicProjectionOutboxRetryBackoff(0), true), PublicProjectionRetryDisposition::BlockedWithoutAttemptBudget);
        }
        if ($classification === PublicProjectionOutboxRetryClassification::Permanent) {
            return new PublicProjectionRetrySchedule(new PublicProjectionOutboxRetryDecision($classification, new PublicProjectionOutboxRetryBackoff(0), false), PublicProjectionRetryDisposition::RetryDenied);
        }
        if ($attempts->value >= $this->maximumAttempts) {
            return new PublicProjectionRetrySchedule(new PublicProjectionOutboxRetryDecision($classification, new PublicProjectionOutboxRetryBackoff(0), false), PublicProjectionRetryDisposition::AttemptsExhausted);
        }

        return new PublicProjectionRetrySchedule(new PublicProjectionOutboxRetryDecision($classification, $this->backoff->forAttempt($attempts), true), PublicProjectionRetryDisposition::RetryAllowed);
    }
}
