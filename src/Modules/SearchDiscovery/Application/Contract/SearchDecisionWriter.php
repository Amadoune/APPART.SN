<?php

namespace Appart\Modules\SearchDiscovery\Application\Contract;

use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecision;
use Appart\Modules\SearchDiscovery\Application\Decision\SearchDecisionWriteResult;

interface SearchDecisionWriter
{
    public function store(SearchDecision $decision): SearchDecisionWriteResult;
}
