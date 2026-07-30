<?php

namespace Appart\Modules\SearchDiscovery\Application\Decision;

use Appart\Modules\SearchDiscovery\Domain\Model\SearchProjection;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\ListingId;
use Appart\Modules\SearchDiscovery\Domain\ValueObject\SearchIndexId;
use InvalidArgumentException;

final readonly class SearchDecision
{
    public function __construct(
        public SearchIndexId $decisionId,
        public ListingId $listingId,
        public int $version,
        public SearchProjection $projection,
    ) {
        if ($version < 1) {
            throw new InvalidArgumentException('A final Search decision version must be positive.');
        }
    }
}
