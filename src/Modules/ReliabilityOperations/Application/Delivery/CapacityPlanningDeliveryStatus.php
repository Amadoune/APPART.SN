<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

enum CapacityPlanningDeliveryStatus: string
{
    case Sufficient = 'sufficient';
    case AtRisk = 'at_risk';
    case Exhausted = 'exhausted';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
