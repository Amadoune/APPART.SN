<?php

namespace Appart\Modules\SecurityCompliance\Application\Outbox;

enum SecurityComplianceOutboxRetryResult: string
{
    case RetryScheduled = 'retry_scheduled';
    case AttemptsExhausted = 'attempts_exhausted';
    case AlreadyCompleted = 'already_completed';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
