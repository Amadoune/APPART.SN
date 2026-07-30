<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionDecisionContext;

enum AdministrativeActionReasonEvidence: string
{
    case Present = 'present';
    case Missing = 'missing';
}
