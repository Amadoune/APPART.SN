<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

enum LegacyMigrationReconciliationEventStatus: string
{
    case Matched = 'matched';
    case Divergent = 'divergent';
    case Pending = 'pending';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
