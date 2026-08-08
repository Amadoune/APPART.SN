<?php

namespace Appart\Modules\ListingLifecycle\Application\ContactPrincipalBoundary\Contract;

use Appart\Modules\ListingLifecycle\Application\ContactPrincipalBoundary\ContactPrincipalObservedAt;
use Appart\Modules\ListingLifecycle\Application\ContactPrincipalBoundary\ListingContactPrincipalResultV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

interface ListingContactPrincipalReaderV1
{
    public function read(
        ListingId $listingId,
        ContactPrincipalObservedAt $observedAt,
    ): ListingContactPrincipalResultV1;
}
