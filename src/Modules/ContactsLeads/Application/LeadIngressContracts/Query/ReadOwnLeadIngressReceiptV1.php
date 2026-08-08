<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Query;

use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressId;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressObservedAt;

final readonly class ReadOwnLeadIngressReceiptV1
{
    public function __construct(
        public LeadIngressId $leadIngressId,
        public string $requesterReference,
        public LeadIngressObservedAt $observedAt,
    ) {}
}
