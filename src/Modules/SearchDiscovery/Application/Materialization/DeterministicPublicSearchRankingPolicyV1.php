<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization;

use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\PublicSearchRankingPolicyV1;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchRank;

final readonly class DeterministicPublicSearchRankingPolicyV1 implements PublicSearchRankingPolicyV1
{
    public const string POLICY_ID = 'public-search-ranking-policy-v1';

    public function decide(): PublicSearchRankingDecisionV1
    {
        return new PublicSearchRankingDecisionV1(self::POLICY_ID, SearchRank::fromInt(0), []);
    }
}
