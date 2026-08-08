<?php

namespace Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\Contract;

use Appart\Modules\SearchDiscovery\Application\SearchOwnerSource\SearchOwnerReadResult;
use Appart\Modules\SearchDiscovery\Application\SearchOwnerSourceRuntimeRead\SearchOwnerSourceRuntimeReadResult;

interface SearchOwnerSourceRuntimeReadPolicy
{
    public function reduce(SearchOwnerReadResult $sourceResult): SearchOwnerSourceRuntimeReadResult;
}
