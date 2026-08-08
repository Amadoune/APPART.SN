<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead;

enum ReservationAvailabilityOwnerSourceRuntimeReadAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
