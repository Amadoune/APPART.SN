<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

enum LeadRejectionReason: string
{
    case Duplicate = 'duplicate';
    case Spam = 'spam';
    case InvalidContactDetails = 'invalid_contact_details';
    case Abuse = 'abuse';
    case IneligibleRequest = 'ineligible_request';
}
