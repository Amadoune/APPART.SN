<?php

namespace Appart\Modules\Media\Application\ReadyAsset;

enum MediaAssetReadinessStatus: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case Missing = 'missing';
    case InvalidState = 'invalid_state';
    case IntegrityFailure = 'integrity_failure';
    case VersionConflict = 'version_conflict';
    case DependencyUnavailable = 'dependency_unavailable';
}
