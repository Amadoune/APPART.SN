<?php

namespace Appart\Modules\ContactsLeads\Application\LeadLifecycle;

enum LeadLifecycleState: string
{
    case Created = 'created';
    case Delivered = 'delivered';
    case Rejected = 'rejected';
    case Closed = 'closed';
}
