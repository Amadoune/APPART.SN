<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Outbox;

enum ExperienceAcceptanceOutboxMessageStatus: string
{
    case Pending = 'pending';
    case Claimed = 'claimed';
    case RetryScheduled = 'retry_scheduled';
    case Completed = 'completed';
    case AttemptsExhausted = 'attempts_exhausted';
}
