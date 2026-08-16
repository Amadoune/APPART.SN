<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization\Contract;

use Appart\Modules\SearchDiscovery\Application\Materialization\PublicSearchRankingDecisionV1;

interface PublicSearchRankingPolicyV1
{
    public function decide(): PublicSearchRankingDecisionV1;
}
