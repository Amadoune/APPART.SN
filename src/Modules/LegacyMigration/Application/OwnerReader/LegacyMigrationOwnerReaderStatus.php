<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerReader;

enum LegacyMigrationOwnerReaderStatus: string
{
    case Available = 'available';
    case Ready = 'ready';
    case Blocked = 'blocked';
    case Completed = 'completed';
    case Matched = 'matched';
    case Divergent = 'divergent';
    case Pending = 'pending';
    case Empty = 'empty';
    case ContainsItems = 'contains_items';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
