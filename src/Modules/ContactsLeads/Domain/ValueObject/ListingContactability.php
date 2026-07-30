<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

enum ListingContactability: string
{
    case Contactable = 'contactable';
    case Missing = 'missing';
    case NotPublished = 'not_published';
    case Closed = 'closed';
}
