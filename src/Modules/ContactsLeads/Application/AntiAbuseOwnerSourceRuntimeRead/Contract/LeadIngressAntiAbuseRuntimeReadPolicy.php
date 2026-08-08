<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\Contract;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSource\AntiAbuseRevisionReadResult;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead\LeadIngressAntiAbuseRuntimeReadResult;

interface LeadIngressAntiAbuseRuntimeReadPolicy
{
    public function reduce(AntiAbuseRevisionReadResult $sourceResult): LeadIngressAntiAbuseRuntimeReadResult;
}
