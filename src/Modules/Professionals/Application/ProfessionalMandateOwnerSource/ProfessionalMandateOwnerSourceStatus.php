<?php

namespace Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource;

enum ProfessionalMandateOwnerSourceStatus: string
{
    case Resolved = 'resolved';
    case NotMandated = 'not_mandated';
    case Ambiguous = 'ambiguous';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
