<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead;

enum ReservationAvailabilityStatusV1: string
{
    case Available = 'available';
    case Conflicting = 'conflicting';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
