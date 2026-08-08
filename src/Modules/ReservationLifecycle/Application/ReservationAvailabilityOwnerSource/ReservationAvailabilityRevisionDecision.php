<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource;

enum ReservationAvailabilityRevisionDecision: string
{
    case Proposed = 'proposed';
    case Held = 'held';
    case Committed = 'committed';
    case Released = 'released';
    case Expired = 'expired';

    public function blocksAvailability(): bool
    {
        return $this === self::Held || $this === self::Committed;
    }
}
