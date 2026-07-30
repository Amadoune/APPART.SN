<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

enum LeadStatus: string
{
    case Created = 'created';
    case Delivered = 'delivered';
    case Rejected = 'rejected';
    case Closed = 'closed';
}
