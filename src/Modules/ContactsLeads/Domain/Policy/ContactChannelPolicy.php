<?php

namespace Appart\Modules\ContactsLeads\Domain\Policy;

use Appart\Modules\ContactsLeads\Domain\Exception\LeadViolation;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ContactChannel;
use Appart\Modules\ContactsLeads\Domain\ValueObject\VisitorIdentity;

final readonly class ContactChannelPolicy
{
    public function assertAvailable(ContactChannel $channel, VisitorIdentity $visitor): void
    {
        if ($channel === ContactChannel::Email && $visitor->contacts->email === null) {
            throw new LeadViolation('Email channel requires an email address.');
        }
        if (($channel === ContactChannel::Phone || $channel === ContactChannel::WhatsApp) && $visitor->contacts->phone === null) {
            throw new LeadViolation('Phone-based channel requires a phone number.');
        }
    }
}
