<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

enum AdvertiserEligibility: string
{
    case EligibleRecipient = 'eligible_recipient';
    case Missing = 'missing';
    case Suspended = 'suspended';
    case NotListingRecipient = 'not_listing_recipient';
}
