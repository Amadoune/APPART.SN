<?php

namespace Appart\Modules\RealEstateCatalog\Application\ReservationEligibilityPublicRead\Contract;

use Appart\Modules\RealEstateCatalog\Application\ReservationEligibilityPublicRead\PropertyReservationEligibilityObservedAt;
use Appart\Modules\RealEstateCatalog\Application\ReservationEligibilityPublicRead\PropertyReservationEligibilityResultV1;
use Appart\Modules\RealEstateCatalog\Domain\ValueObject\PropertyId;

interface PropertyReservationEligibilityReaderV1
{
    public function read(
        PropertyId $propertyId,
        PropertyReservationEligibilityObservedAt $observedAt,
    ): PropertyReservationEligibilityResultV1;
}
