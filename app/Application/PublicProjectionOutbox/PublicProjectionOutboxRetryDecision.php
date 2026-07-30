<?php

namespace App\Application\PublicProjectionOutbox;

final readonly class PublicProjectionOutboxRetryDecision
{
    public function __construct(
        public PublicProjectionOutboxRetryClassification $classification,
        public PublicProjectionOutboxRetryBackoff $backoff,
        public bool $retryAllowed,
    ) {}
}
