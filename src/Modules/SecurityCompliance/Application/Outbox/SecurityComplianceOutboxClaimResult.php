<?php

namespace Appart\Modules\SecurityCompliance\Application\Outbox;

enum SecurityComplianceOutboxClaimResult: string
{
    case Claimed = 'claimed';
    case AlreadyClaimed = 'already_claimed';
    case AlreadyCompleted = 'already_completed';
    case AttemptsExhausted = 'attempts_exhausted';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
