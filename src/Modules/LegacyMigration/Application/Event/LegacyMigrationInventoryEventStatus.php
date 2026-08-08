<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

enum LegacyMigrationInventoryEventStatus: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
