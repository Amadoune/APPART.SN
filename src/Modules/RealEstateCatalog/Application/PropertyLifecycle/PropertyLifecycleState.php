<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle;

enum PropertyLifecycleState: string
{
    case Draft = 'draft';
    case Active = 'active';
    case UnderMaintenance = 'under_maintenance';
    case Unavailable = 'unavailable';
    case Decommissioned = 'decommissioned';
    case Archived = 'archived';
}
