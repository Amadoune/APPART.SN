<?php

namespace Appart\Modules\RealEstateCatalog\Application\AuthoringPersistence;

enum PropertyAuthoringPersistenceWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentIntent = 'divergent_intent';
    case VersionConflict = 'version_conflict';
    case IdentityConflict = 'identity_conflict';
    case Rejected = 'rejected';
}
