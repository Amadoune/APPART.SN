<?php

namespace Appart\Modules\Professionals\Application\ProfessionalLeadRecipientPublicRead;

enum ProfessionalLeadRecipientStatusV1: string
{
    case Eligible = 'eligible';
    case NotEligible = 'not_eligible';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
    case DependencyUnavailable = 'dependency_unavailable';
}
