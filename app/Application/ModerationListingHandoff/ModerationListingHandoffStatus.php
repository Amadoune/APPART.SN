<?php

namespace App\Application\ModerationListingHandoff;

enum ModerationListingHandoffStatus: string
{
    case Requested = 'requested';
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case Rejected = 'rejected';
    case VersionConflict = 'version_conflict';
    case AuthorizationDenied = 'authorization_denied';
    case TargetIneligible = 'target_ineligible';
    case DependencyUnavailable = 'dependency_unavailable';
    case Quarantined = 'quarantined';
}
