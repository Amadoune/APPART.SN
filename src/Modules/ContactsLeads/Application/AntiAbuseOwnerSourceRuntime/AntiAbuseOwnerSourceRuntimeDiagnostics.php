<?php

namespace Appart\Modules\ContactsLeads\Application\AntiAbuseOwnerSourceRuntime;

final readonly class AntiAbuseOwnerSourceRuntimeDiagnostics
{
    public function __construct(
        public string $runtimeId,
        public string $version,
        public AntiAbuseOwnerSourceRuntimeAvailability $availability,
    ) {}
}
