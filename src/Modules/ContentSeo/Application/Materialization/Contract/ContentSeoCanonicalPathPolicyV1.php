<?php

namespace Appart\Modules\ContentSeo\Application\Materialization\Contract;

use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

interface ContentSeoCanonicalPathPolicyV1
{
    public function decide(ListingId $listingId): string;
}
