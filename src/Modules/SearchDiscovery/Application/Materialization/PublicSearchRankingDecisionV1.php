<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization;

use Appart\Modules\SearchDiscovery\Domain\Model\SearchFacet;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchRank;

final readonly class PublicSearchRankingDecisionV1
{
    /** @param list<SearchFacet> $facets */
    public function __construct(
        public string $policyId,
        public SearchRank $rank,
        public array $facets,
    ) {}
}
