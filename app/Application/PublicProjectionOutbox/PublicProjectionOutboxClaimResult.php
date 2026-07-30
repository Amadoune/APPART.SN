<?php

namespace App\Application\PublicProjectionOutbox;

enum PublicProjectionOutboxClaimResult: string
{
    case Claimed = 'claimed';
    case AlreadyClaimed = 'already_claimed';
    case LeaseExpired = 'lease_expired';
    case Released = 'released';
    case RetryScheduled = 'retry_scheduled';
    case Quarantined = 'quarantined';
    case BlockedBySequenceGap = 'blocked_by_sequence_gap';
    case BlockedBySourceReadiness = 'blocked_by_source_readiness';
    case NothingToClaim = 'nothing_to_claim';
    case ConsumerBehind = 'consumer_behind';
    case Abandoned = 'abandoned';
}
