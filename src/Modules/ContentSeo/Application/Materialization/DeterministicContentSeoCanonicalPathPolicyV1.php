<?php

namespace Appart\Modules\ContentSeo\Application\Materialization;

use Appart\Modules\ContentSeo\Application\Materialization\Contract\ContentSeoCanonicalPathPolicyV1;
use Appart\Modules\ContentSeo\Domain\ValueObject\ListingId;

final readonly class DeterministicContentSeoCanonicalPathPolicyV1 implements ContentSeoCanonicalPathPolicyV1
{
    public function decide(ListingId $listingId): string
    {
        return 'annonces/'.$listingId->value;
    }
}
