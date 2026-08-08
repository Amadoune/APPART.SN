<?php

namespace Appart\Modules\SecurityCompliance\Application\Outbox;

enum SecurityComplianceOutboxMessageStatus: string
{
    case Pending = 'pending';
    case Claimed = 'claimed';
    case RetryScheduled = 'retry_scheduled';
    case Completed = 'completed';
    case AttemptsExhausted = 'attempts_exhausted';
}
