<?php

namespace Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Contract;

use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Result\LeadIngressAntiAbuseResultV1;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;

interface LeadIngressAntiAbuseReaderV1
{
    public function read(
        LeadIngressIntentId $intentId,
        LeadIngressAntiAbuseObservedAt $observedAt,
    ): LeadIngressAntiAbuseResultV1;
}
