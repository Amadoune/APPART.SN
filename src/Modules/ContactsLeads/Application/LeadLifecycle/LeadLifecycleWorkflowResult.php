<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycle;

enum LeadLifecycleWorkflowResult: string
{
    case Allowed = 'allowed';
    case Denied = 'denied';
}
