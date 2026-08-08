<?php

namespace Appart\Modules\ListingLifecycle\Application\ReservationAvailabilityPublicRead\Contract;

use Appart\Modules\ListingLifecycle\Application\ReservationAvailabilityPublicRead\ListingReservationAvailabilityObservedAt;
use Appart\Modules\ListingLifecycle\Application\ReservationAvailabilityPublicRead\ListingReservationAvailabilityResultV1;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;

interface ListingReservationAvailabilityReaderV1
{
    public function read(
        ListingId $listingId,
        ListingReservationAvailabilityObservedAt $observedAt,
    ): ListingReservationAvailabilityResultV1;
}
