<?php

namespace Appart\Modules\ListingLifecycle\Application\ReservationAvailabilityPublicRead;

enum ListingReservationAvailabilityStatusV1: string
{
    case Reservable = 'reservable';
    case NotReservable = 'not_reservable';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
