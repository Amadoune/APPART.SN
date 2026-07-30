<?php

namespace Appart\Modules\Professionals\Application\ProfessionalMandateResolution;

enum ProfessionalMandateResolutionStatusV1: string
{
    case Resolved = 'resolved';
    case NotMandated = 'not_mandated';
    case Ambiguous = 'ambiguous';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
