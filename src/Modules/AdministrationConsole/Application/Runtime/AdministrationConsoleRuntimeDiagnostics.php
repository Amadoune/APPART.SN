<?php

namespace Appart\Modules\AdministrationConsole\Application\Runtime;

final readonly class AdministrationConsoleRuntimeDiagnostics
{
    public function __construct(
        public string $runtimeId,
        public string $version,
        public AdministrationConsoleRuntimeAvailability $availability,
    ) {}
}
