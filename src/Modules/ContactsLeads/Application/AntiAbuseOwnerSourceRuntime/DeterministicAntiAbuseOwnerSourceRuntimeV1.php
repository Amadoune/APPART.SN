<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime;

use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\Contract\AntiAbuseOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime\Contract\AntiAbuseOwnerSourceRuntimeV1;

final readonly class DeterministicAntiAbuseOwnerSourceRuntimeV1 implements AntiAbuseOwnerSourceRuntimeV1
{
    private const RUNTIME_ID = 'contacts-leads.anti-abuse-owner-source';

    private const VERSION = 'anti-abuse-owner-source-runtime-v1';

    public function __construct(private AntiAbuseOwnerSourceRuntimeAvailabilityPolicy $availabilityPolicy) {}

    public function availability(): AntiAbuseOwnerSourceRuntimeAvailability
    {
        return $this->availabilityPolicy->inspect();
    }

    public function diagnostics(): AntiAbuseOwnerSourceRuntimeDiagnostics
    {
        return new AntiAbuseOwnerSourceRuntimeDiagnostics(
            self::RUNTIME_ID,
            self::VERSION,
            $this->availability(),
        );
    }
}
