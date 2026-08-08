<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Result;

use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressId;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressOccurredAt;

final readonly class LeadIngressReceiptV1
{
    public function __construct(
        public LeadIngressId $leadIngressId,
        public LeadIngressSubmissionStatusV1 $status,
        public LeadIngressOccurredAt $recordedAt,
    ) {}
}
