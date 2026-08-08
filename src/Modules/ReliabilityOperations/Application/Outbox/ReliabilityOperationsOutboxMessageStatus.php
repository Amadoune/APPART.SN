<?php

namespace Appart\Modules\ReliabilityOperations\Application\Outbox;

enum ReliabilityOperationsOutboxMessageStatus: string
{
    case Pending = 'pending';
    case Claimed = 'claimed';
    case RetryScheduled = 'retry_scheduled';
    case Completed = 'completed';
    case AttemptsExhausted = 'attempts_exhausted';
}
