<?php

namespace Appart\Modules\LegacyMigration\Application\PublicRead;

enum LegacyMigrationReconciliationStatusV1: string
{
    case Matched = 'matched';
    case Divergent = 'divergent';
    case Pending = 'pending';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
