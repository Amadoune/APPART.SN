<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Command;

use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressOccurredAt;

final readonly class SubmitLeadIngressV1
{
    public function __construct(
        public LeadIngressIntentId $intentId,
        public string $checksum,
        public string $listingId,
        public string $requesterReference,
        public string $contactReference,
        public string $messageReference,
        public LeadIngressObservedAt $observedAt,
        public LeadIngressOccurredAt $occurredAt,
    ) {}
}
