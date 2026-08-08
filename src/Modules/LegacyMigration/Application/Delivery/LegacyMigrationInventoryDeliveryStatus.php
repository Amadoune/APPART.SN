<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

enum LegacyMigrationInventoryDeliveryStatus: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
