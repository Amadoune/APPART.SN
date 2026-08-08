<?php

namespace Appart\Modules\LegacyMigration\Application\OwnerSource;

enum LegacyMigrationReconciliationWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentRevision = 'divergent_revision';
    case VersionConflict = 'version_conflict';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
