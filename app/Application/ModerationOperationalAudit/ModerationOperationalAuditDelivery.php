<?php

namespace App\Application\ModerationOperationalAudit;

use App\Application\ModerationOperationalAuditEventProduction\ModerationOperationalAuditOutboxMessageV1;

final readonly class ModerationOperationalAuditDelivery
{
    public function __construct(
        public ModerationOperationalAuditOutboxMessageV1 $message,
        public int $attempt,
        public string $claimOwner,
    ) {}
}
