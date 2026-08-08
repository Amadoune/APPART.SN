<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource;

enum ReservationAvailabilityReadStatus: string
{
    case Available = 'available';
    case Conflicting = 'conflicting';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
