<?php

namespace Appart\Modules\SearchDiscovery\Application\Materialization;

use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\CatchUpPublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Application\Materialization\Contract\MaterializePublicSearchDecisionV1;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;

final readonly class DeterministicCatchUpPublicSearchDecisionV1 implements CatchUpPublicSearchDecisionV1
{
    public function __construct(private MaterializePublicSearchDecisionV1 $materializer) {}

    public function catchUp(ListingId $listingId): PublicSearchMaterializationResult
    {
        return $this->materializer->materialize($listingId);
    }
}
