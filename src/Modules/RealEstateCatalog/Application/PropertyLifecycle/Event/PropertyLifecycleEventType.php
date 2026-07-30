<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event;

enum PropertyLifecycleEventType: string
{
    case PropertyActivated = 'property.lifecycle.activated';
    case PropertyArchived = 'property.lifecycle.archived';
    case PropertyMaintenanceStarted = 'property.lifecycle.maintenance_started';
    case PropertyMaintenanceCompleted = 'property.lifecycle.maintenance_completed';
    case PropertyMarkedUnavailable = 'property.lifecycle.marked_unavailable';
    case PropertyAvailabilityRestored = 'property.lifecycle.availability_restored';
    case PropertyDecommissioned = 'property.lifecycle.decommissioned';
}
