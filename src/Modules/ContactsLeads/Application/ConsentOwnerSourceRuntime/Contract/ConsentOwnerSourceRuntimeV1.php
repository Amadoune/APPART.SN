<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\Contract;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\ConsentOwnerSourceRuntimeAvailability;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\ConsentOwnerSourceRuntimeDiagnostics;

interface ConsentOwnerSourceRuntimeV1
{
    public function availability(): ConsentOwnerSourceRuntimeAvailability;

    public function diagnostics(): ConsentOwnerSourceRuntimeDiagnostics;
}
