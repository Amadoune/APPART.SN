<?php

namespace Appart\Modules\LegacyMigration\Application\PublicRead;

enum LegacyMigrationCutoverStatusV1: string
{
    case Ready = 'ready';
    case Blocked = 'blocked';
    case Completed = 'completed';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
