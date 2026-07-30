<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycleEvent;

enum LeadLifecycleEventType: string
{
    case Delivered = 'lead.lifecycle.delivered';
    case Rejected = 'lead.lifecycle.rejected';
    case Closed = 'lead.lifecycle.closed';
}
