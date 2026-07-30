<?php

namespace Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource;

enum ProfessionalMandateOwnerSourceWriteResult: string
{
    case Applied = 'applied';
    case AlreadyApplied = 'already_applied';
    case DivergentIntent = 'divergent_intent';
    case VersionConflict = 'version_conflict';
    case Rejected = 'rejected';
}
