<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntimeRead;

final readonly class LeadIngressAntiAbuseRuntimeReadDiagnostics
{
    public function __construct(
        public string $runtimeReadId,
        public string $version,
        public LeadIngressAntiAbuseRuntimeReadAvailability $availability,
    ) {}
}
