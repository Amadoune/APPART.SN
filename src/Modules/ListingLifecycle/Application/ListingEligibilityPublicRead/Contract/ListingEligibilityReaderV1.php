<?php

namespace Appart\Modules\ListingLifecycle\Application\ListingEligibilityPublicRead\Contract;

use Appart\Modules\ListingLifecycle\Application\ListingEligibilityPublicRead\ListingEligibilityObservedAt;
use Appart\Modules\ListingLifecycle\Application\ListingEligibilityPublicRead\ListingEligibilityResultV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

interface ListingEligibilityReaderV1
{
    public function read(
        ListingId $listingId,
        ListingEligibilityObservedAt $observedAt,
    ): ListingEligibilityResultV1;
}
