<?php

namespace Appart\Modules\ModerationReports\Application\ModerationPersistence;

enum ModerationPersistenceWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentIntent = 'divergent_intent';
    case VersionConflict = 'version_conflict';
    case IdentityConflict = 'identity_conflict';
    case Rejected = 'rejected';
}
