<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionTransitionContext;

enum AdministrativeActionContextualInspectionStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}
