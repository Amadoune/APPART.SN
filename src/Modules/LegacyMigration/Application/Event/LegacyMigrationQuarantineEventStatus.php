<?php

namespace Appart\Modules\LegacyMigration\Application\Event;

enum LegacyMigrationQuarantineEventStatus: string
{
    case Empty = 'empty';
    case ContainsItems = 'contains_items';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
