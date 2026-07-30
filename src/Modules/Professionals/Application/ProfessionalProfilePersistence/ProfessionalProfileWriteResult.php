<?php

namespace Appart\Modules\Professionals\Application\ProfessionalProfilePersistence;

enum ProfessionalProfileWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentIntent = 'divergent_intent';
    case VersionConflict = 'version_conflict';
    case CheckpointRegression = 'checkpoint_regression';
    case Rejected = 'rejected';
}
