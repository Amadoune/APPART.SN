<?php

namespace Appart\Modules\ListingLifecycle\Application\ModerationBoundary\Contract;

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationEligibilityV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use DateTimeImmutable;

interface ListingModerationReaderV1
{
    public function read(
        ListingId $listingId,
        DateTimeImmutable $observedAt,
    ): ListingModerationEligibilityV1;
}
