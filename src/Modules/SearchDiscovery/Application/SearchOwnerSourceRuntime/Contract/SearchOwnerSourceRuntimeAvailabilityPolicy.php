<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\Contract;

use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntime\SearchOwnerSourceRuntimeAvailability;

interface SearchOwnerSourceRuntimeAvailabilityPolicy
{
    public function inspect(): SearchOwnerSourceRuntimeAvailability;
}
