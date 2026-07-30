<?php

namespace Appart\Modules\ListingLifecycle\Application\AuthoringPersistence;

enum AuthoringPersistenceWriteResult: string
{
    case Applied = 'Applied';
    case AlreadyApplied = 'AlreadyApplied';
    case DivergentIntent = 'DivergentIntent';
    case VersionConflict = 'VersionConflict';
    case IdentityConflict = 'IdentityConflict';
    case Rejected = 'Rejected';
}
