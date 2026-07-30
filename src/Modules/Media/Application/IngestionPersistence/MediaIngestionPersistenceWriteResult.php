<?php

namespace Appart\Modules\Media\Application\IngestionPersistence;

enum MediaIngestionPersistenceWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentIntent = 'divergent_intent';
    case VersionConflict = 'version_conflict';
    case IdentityConflict = 'identity_conflict';
    case Rejected = 'rejected';
}
