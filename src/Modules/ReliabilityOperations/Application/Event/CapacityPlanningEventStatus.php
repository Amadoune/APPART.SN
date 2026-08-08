<?php

namespace Appart\Modules\ReliabilityOperations\Application\Event;

enum CapacityPlanningEventStatus: string
{
    case Sufficient = 'sufficient';
    case AtRisk = 'at_risk';
    case Exhausted = 'exhausted';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
