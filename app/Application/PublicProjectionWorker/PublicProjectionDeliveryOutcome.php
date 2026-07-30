<?php

namespace App\Application\PublicProjectionWorker;

enum PublicProjectionDeliveryOutcome: string
{
    case Delivered = 'delivered';
    case AlreadyConsumed = 'already_consumed';
    case Obsolete = 'obsolete';
    case RetryScheduled = 'retry_scheduled';
    case BlockedBySequenceGap = 'blocked_by_sequence_gap';
    case BlockedBySourceReadiness = 'blocked_by_source_readiness';
    case Quarantined = 'quarantined';
    case ClaimConflict = 'claim_conflict';
    case LeaseExpired = 'lease_expired';
    case Unsupported = 'unsupported';
    case TechnicalFailure = 'technical_failure';
    case DeferredByCausality = 'deferred_by_causality';
}
