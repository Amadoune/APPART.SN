<?php

namespace App\Application\PublicProjectionDelivery;

enum PublicProjectionDeliveryStatus: string
{
    case Pending = 'pending';
    case Claimed = 'claimed';
    case RetryScheduled = 'retry_scheduled';
    case BlockedBySequenceGap = 'blocked_by_sequence_gap';
    case BlockedBySourceReadiness = 'blocked_by_source_readiness';
    case Delivered = 'delivered';
    case Quarantined = 'quarantined';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::Claimed, self::Quarantined], true),
            self::Claimed => in_array($target, [self::Delivered, self::RetryScheduled, self::BlockedBySequenceGap, self::BlockedBySourceReadiness, self::Quarantined], true),
            self::RetryScheduled, self::BlockedBySequenceGap, self::BlockedBySourceReadiness => in_array($target, [self::Claimed, self::Quarantined], true),
            self::Delivered, self::Quarantined => false,
        };
    }
}
