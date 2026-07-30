<?php

namespace Appart\Modules\ContactsLeads\Domain\Policy;

use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactMessage;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactSubject;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadSignature;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\VisitorIdentity;

final readonly class LeadSignaturePolicy
{
    public function create(ListingId $listing, VisitorIdentity $visitor, ContactSubject $subject, ContactMessage $message): LeadSignature
    {
        return LeadSignature::fromCanonicalInput(implode('|', [
            $listing->value,
            $visitor->id->value,
            $subject->value,
            mb_strtolower($message->value),
        ]));
    }
}
