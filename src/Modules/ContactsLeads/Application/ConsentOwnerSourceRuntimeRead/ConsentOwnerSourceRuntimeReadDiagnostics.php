<?php

namespace Appart\Modules\ContactsLeads\Application\ConsentOwnerSourceRuntimeRead;

final readonly class ConsentOwnerSourceRuntimeReadDiagnostics
{
    public function __construct(
        public string $runtimeVersion,
        public string $component,
    ) {}
}
