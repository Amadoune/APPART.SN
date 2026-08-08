<?php

namespace Appart\Modules\ReliabilityOperations\Application\Delivery;

enum OperationalReadinessDeliveryStatus: string
{
    case Ready = 'ready';
    case AtRisk = 'at_risk';
    case Blocked = 'blocked';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
