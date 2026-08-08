<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\Contract;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\AntiAbuseOwnerSourceRuntimeAvailability;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\AntiAbuseOwnerSourceRuntimeDiagnostics;

interface AntiAbuseOwnerSourceRuntimeV1
{
    public function availability(): AntiAbuseOwnerSourceRuntimeAvailability;

    public function diagnostics(): AntiAbuseOwnerSourceRuntimeDiagnostics;
}
