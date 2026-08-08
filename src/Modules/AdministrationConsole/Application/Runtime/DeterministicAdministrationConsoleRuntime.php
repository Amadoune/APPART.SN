<?php

namespace Appart\Modules\AdministrationConsole\Application\Runtime;

final readonly class DeterministicAdministrationConsoleRuntime implements AdministrationConsoleRuntimeV1
{
    private const RUNTIME_ID = 'administration-console.owner-source';

    private const VERSION = 'administration-console-runtime-v1';

    public function __construct(private AdministrationConsoleRuntimeAvailabilityPolicy $availabilityPolicy) {}

    public function availability(): AdministrationConsoleRuntimeAvailability
    {
        return $this->availabilityPolicy->inspect();
    }

    public function diagnostics(): AdministrationConsoleRuntimeDiagnostics
    {
        return new AdministrationConsoleRuntimeDiagnostics(self::RUNTIME_ID, self::VERSION, $this->availability());
    }
}
