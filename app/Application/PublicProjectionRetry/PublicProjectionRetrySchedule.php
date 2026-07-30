<?php

namespace App\Application\PublicProjectionRetry;

use App\Application\PublicProjectionOutbox\PublicProjectionOutboxRetryDecision;
use DateInterval;
use DateTimeImmutable;

final readonly class PublicProjectionRetrySchedule
{
    public function __construct(
        public PublicProjectionOutboxRetryDecision $decision,
        public PublicProjectionRetryDisposition $disposition,
    ) {}

    public function availableAt(DateTimeImmutable $failedAt): ?DateTimeImmutable
    {
        if ($this->disposition !== PublicProjectionRetryDisposition::RetryAllowed) {
            return null;
        }

        return $failedAt->add(new DateInterval("PT{$this->decision->backoff->delaySeconds}S"));
    }
}
