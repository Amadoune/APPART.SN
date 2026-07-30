<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext;

enum AdministrativeActionDecisionContextResolutionStatus: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
}
