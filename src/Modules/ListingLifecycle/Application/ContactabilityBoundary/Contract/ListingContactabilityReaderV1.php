<?php

namespace Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\Contract;

use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\ContactabilityObservedAt;
use Appart\Modules\ListingLifecycle\Application\ContactabilityBoundary\ListingContactabilityDecisionV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

interface ListingContactabilityReaderV1
{
    public function read(
        ListingId $listingId,
        ContactabilityObservedAt $observedAt,
    ): ListingContactabilityDecisionV1;
}
