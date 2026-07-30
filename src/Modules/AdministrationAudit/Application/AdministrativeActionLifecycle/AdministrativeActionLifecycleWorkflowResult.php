<?php

namespace Appart\Modules\AdministrationAudit\Application\AdministrativeActionLifecycle;

enum AdministrativeActionLifecycleWorkflowResult: string
{
    case Allowed = 'allowed';
    case Denied = 'denied';
}
