<?php

namespace Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext;

enum ProfessionalStatusContextualInspectionStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}
