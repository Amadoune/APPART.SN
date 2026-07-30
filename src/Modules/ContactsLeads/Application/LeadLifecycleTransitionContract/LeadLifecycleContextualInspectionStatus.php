<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract;

enum LeadLifecycleContextualInspectionStatus: string
{
    case Found = 'found';
    case Missing = 'missing';
    case Corrupted = 'corrupted';
}
