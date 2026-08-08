<?php

namespace Appart\Modules\AdministrationConsole\Application\Runtime;

interface AdministrationConsoleRuntimeV1
{
    public function availability(): AdministrationConsoleRuntimeAvailability;

    public function diagnostics(): AdministrationConsoleRuntimeDiagnostics;
}
