<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\Contract;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\AntiAbuseOwnerSourceRuntimeAvailability;

interface AntiAbuseOwnerSourceRuntimeAvailabilityPolicy
{
    public function inspect(): AntiAbuseOwnerSourceRuntimeAvailability;
}
