<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime;

use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\Contract\ConsentOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime\Contract\ConsentOwnerSourceRuntimeV1;

final readonly class DeterministicConsentOwnerSourceRuntimeV1 implements ConsentOwnerSourceRuntimeV1
{
    private const VERSION = 'consent-owner-source-runtime-v1';

    private const COMPONENT = 'contacts-leads.consent-owner-source';

    public function __construct(private ConsentOwnerSourceRuntimeAvailabilityPolicy $availabilityPolicy) {}

    public function availability(): ConsentOwnerSourceRuntimeAvailability
    {
        return $this->availabilityPolicy->inspect();
    }

    public function diagnostics(): ConsentOwnerSourceRuntimeDiagnostics
    {
        return new ConsentOwnerSourceRuntimeDiagnostics(
            self::VERSION,
            self::COMPONENT,
            $this->availability(),
        );
    }
}
