<?php

namespace Appart\Modules\RealEstateCatalog\Application\ReservationEligibilityPublicRead;

use DateTimeImmutable;
use DateTimeZone;

final readonly class PropertyReservationEligibilityObservedAt
{
    public DateTimeImmutable $value;

    public function __construct(DateTimeImmutable $value)
    {
        $this->value = $value->setTimezone(new DateTimeZone('UTC'));
    }

    public function canonical(): string
    {
        return $this->value->format('Y-m-d\TH:i:s.u\Z');
    }
}
