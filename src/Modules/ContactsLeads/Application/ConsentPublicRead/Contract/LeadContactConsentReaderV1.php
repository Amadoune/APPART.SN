<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Contract;

use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Result\LeadContactConsentResultV1;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Value\LeadConsentObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;

interface LeadContactConsentReaderV1
{
    public function read(
        LeadIngressIntentId $intentId,
        LeadConsentObservedAt $observedAt,
    ): LeadContactConsentResultV1;
}
