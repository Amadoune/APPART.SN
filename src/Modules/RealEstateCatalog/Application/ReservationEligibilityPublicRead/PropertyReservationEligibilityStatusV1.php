<?php

namespace Appart\Modules\RealEstateCatalog\Application\ReservationEligibilityPublicRead;

enum PropertyReservationEligibilityStatusV1: string
{
    case Eligible = 'eligible';
    case NotEligible = 'not_eligible';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
