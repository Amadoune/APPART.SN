<?php

namespace App\Application\PublicProjectionRetry;

enum PublicProjectionRetryDisposition: string
{
    case RetryAllowed = 'retry_allowed';
    case RetryDenied = 'retry_denied';
    case AttemptsExhausted = 'attempts_exhausted';
    case BlockedWithoutAttemptBudget = 'blocked_without_attempt_budget';
}
