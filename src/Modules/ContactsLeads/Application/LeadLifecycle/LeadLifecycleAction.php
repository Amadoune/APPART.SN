<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycle;

enum LeadLifecycleAction: string
{
    case Deliver = 'deliver';
    case Reject = 'reject';
    case Close = 'close';
    case Unknown = 'unknown';
}
