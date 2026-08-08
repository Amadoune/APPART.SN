<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\Contract;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadDiagnostics;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead\ConsentOwnerSourceRuntimeReadResult;
use Appart\Modules\ContactsLeads\Application\ConsentPublicRead\Value\LeadConsentObservedAt;
use Appart\Modules\ContactsLeads\Application\LeadIngressContracts\Value\LeadIngressIntentId;

interface ConsentOwnerSourceRuntimeReadV1
{
    public function read(
        LeadIngressIntentId $intentId,
        LeadConsentObservedAt $observedAt,
    ): ConsentOwnerSourceRuntimeReadResult;

    public function diagnostics(): ConsentOwnerSourceRuntimeReadDiagnostics;
}
