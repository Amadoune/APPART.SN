<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle;

enum ReservationLifecycleState: string
{
    case Draft = 'draft';
    case Requested = 'requested';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Rejected = 'rejected';
}
