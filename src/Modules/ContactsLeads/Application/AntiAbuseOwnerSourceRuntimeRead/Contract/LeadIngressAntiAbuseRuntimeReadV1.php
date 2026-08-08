<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\Contract;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadDiagnostics;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadResult;
use Appart\Modules\ContactsLeads\Application\LeadIngressAntiAbusePublicRead\Value\LeadIngressAntiAbuseObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;

interface LeadIngressAntiAbuseRuntimeReadV1
{
    public function read(
        LeadIngressIntentId $intentId,
        LeadIngressAntiAbuseObservedAt $observedAt,
    ): LeadIngressAntiAbuseRuntimeReadResult;

    public function diagnostics(): LeadIngressAntiAbuseRuntimeReadDiagnostics;
}
