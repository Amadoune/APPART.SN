<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead;

enum ReservationAvailabilityOwnerSourceRuntimeReadStatus: string
{
    case Allowed = 'allowed';
    case Conflicting = 'conflicting';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
