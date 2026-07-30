<?php

namespace Appart\Modules\ContactsLeads\Domain\ValueObject;

final readonly class LeadDeduplicationClaim
{
    public function __construct(public LeadSignature $signature, public LeadTimestamp $occurredAt) {}
}
