<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerSource;

enum ReliabilityOperationsStream: string
{
    case Observability = 'observability';
    case ServiceHealth = 'service_health';
    case Alerting = 'alerting';
    case Continuity = 'continuity';
    case MaintenanceOperations = 'maintenance_operations';
    case CapacityPlanning = 'capacity_planning';
    case OperationalReadiness = 'operational_readiness';
}
