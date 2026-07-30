<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead;

enum ProfessionalPublicStatusDecisionV1: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
