<?php

namespace Appart\Modules\LegacyMigration\Application\PublicRead;

enum LegacyMigrationInventoryStatusV1: string
{
    case Available = 'available';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
