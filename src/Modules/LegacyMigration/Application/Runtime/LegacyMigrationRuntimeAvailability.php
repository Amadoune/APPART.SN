<?php

namespace Appart\Modules\LegacyMigration\Application\Runtime;

enum LegacyMigrationRuntimeAvailability: string
{
    case Available = 'available';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
