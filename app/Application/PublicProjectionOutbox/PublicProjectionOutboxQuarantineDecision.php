<?php

namespace App\Application\PublicProjectionOutbox;

final readonly class PublicProjectionOutboxQuarantineDecision
{
    public function __construct(public PublicProjectionOutboxQuarantineReason $reason, public string $errorCode)
    {
        if ($errorCode === '' || trim($errorCode) !== $errorCode) {
            throw new \InvalidArgumentException('Invalid Public Projection Outbox quarantine error code.');
        }
    }
}
