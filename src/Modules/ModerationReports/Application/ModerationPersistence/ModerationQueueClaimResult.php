<?php

namespace Appart\Modules\ModerationReports\Application\ModerationPersistence;

enum ModerationQueueClaimResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentIntent = 'divergent_intent';
    case VersionConflict = 'version_conflict';
    case Claimed = 'claimed';
    case AlreadyClaimed = 'already_claimed';
    case Missing = 'missing';
    case LeaseConflict = 'lease_conflict';
    case Rejected = 'rejected';
}
