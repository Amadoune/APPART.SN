<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\Contract;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\ConsentOwnerSourceRuntimeAvailability;

interface ConsentOwnerSourceRuntimeAvailabilityPolicy
{
    public function inspect(): ConsentOwnerSourceRuntimeAvailability;
}
