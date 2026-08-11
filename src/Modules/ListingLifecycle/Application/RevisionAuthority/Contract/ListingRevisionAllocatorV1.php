<?php

namespace Appart\Modules\ListingLifecycle\Application\RevisionAuthority\Contract;

use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\ListingRevisionIntentId;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\ListingRevisionOperation;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;

interface ListingRevisionAllocatorV1
{
    public function allocate(
        ListingId $listingId,
        ListingRevisionOperation $operation,
        ListingRevisionIntentId $intentId,
    ): ListingRevisionId;
}
