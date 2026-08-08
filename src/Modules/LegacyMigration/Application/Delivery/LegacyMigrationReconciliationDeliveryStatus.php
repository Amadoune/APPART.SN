<?php

namespace Appart\Modules\LegacyMigration\Application\Delivery;

enum LegacyMigrationReconciliationDeliveryStatus: string
{
    case Matched = 'matched';
    case Divergent = 'divergent';
    case Pending = 'pending';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
