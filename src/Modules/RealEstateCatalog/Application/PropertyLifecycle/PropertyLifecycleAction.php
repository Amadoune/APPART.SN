<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

enum PropertyLifecycleAction: string
{
    case Activate = 'activate';
    case BeginMaintenance = 'begin_maintenance';
    case CompleteMaintenance = 'complete_maintenance';
    case MarkUnavailable = 'mark_unavailable';
    case RestoreAvailability = 'restore_availability';
    case Decommission = 'decommission';
    case Archive = 'archive';
    case Unknown = 'unknown';
}
