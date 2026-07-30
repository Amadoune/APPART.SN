<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract;

enum LeadLifecycleEligibilityRequirement: string
{
    case NotRequired = 'not_required';
}
