<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntime;

final readonly class ConsentOwnerSourceRuntimeDiagnostics
{
    public function __construct(
        public string $runtimeVersion,
        public string $component,
        public ConsentOwnerSourceRuntimeAvailability $availability,
    ) {}
}
