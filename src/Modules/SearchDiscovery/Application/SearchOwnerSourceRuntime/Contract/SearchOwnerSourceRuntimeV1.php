<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\Contract;

use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\SearchOwnerSourceRuntimeAvailability;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\SearchOwnerSourceRuntimeDiagnostics;

interface SearchOwnerSourceRuntimeV1
{
    public function availability(): SearchOwnerSourceRuntimeAvailability;

    public function diagnostics(): SearchOwnerSourceRuntimeDiagnostics;
}
