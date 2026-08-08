<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Outbox;

enum ExperienceAcceptanceOutboxClaimResult: string
{
    case Claimed = 'claimed';
    case AlreadyClaimed = 'already_claimed';
    case AlreadyCompleted = 'already_completed';
    case AttemptsExhausted = 'attempts_exhausted';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
