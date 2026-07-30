<?php

namespace App\Application\ModerationOperationalAuditEventProduction\Contract;

use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationOperationalAuditEventProduction\ModerationOperationalAuditOutboxMessageV1;

interface ModerationOperationalAuditOutboxAppenderV1
{
    public function append(
        ModerationOperationalAuditOutboxMessageV1 $message,
    ): ModerationOutboxAppendResult;
}
