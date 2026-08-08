<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

enum LegacyMigrationQuarantineDeliveryStatus: string
{
    case Empty = 'empty';
    case ContainsItems = 'contains_items';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
