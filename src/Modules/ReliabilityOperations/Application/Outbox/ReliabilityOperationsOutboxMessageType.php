<?php

namespace Appart\Modules\ReliabilityOperations\Application\Outbox;

enum ReliabilityOperationsOutboxMessageType: string
{
    case Observability = 'reliability-operations.observability.observed.v1';
    case ServiceHealth = 'reliability-operations.service-health.observed.v1';
    case Alerting = 'reliability-operations.alerting.observed.v1';
    case MaintenanceOperations = 'reliability-operations.maintenance-operations.observed.v1';
    case Continuity = 'reliability-operations.continuity.observed.v1';
    case CapacityPlanning = 'reliability-operations.capacity-planning.observed.v1';
    case OperationalReadiness = 'reliability-operations.operational-readiness.observed.v1';
}
