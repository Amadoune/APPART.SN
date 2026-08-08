<?php

namespace Appart\Modules\AdministrationConsole\Application\Runtime;

interface AdministrationConsoleRuntimeAvailabilityPolicy
{
    public function inspect(): AdministrationConsoleRuntimeAvailability;
}
