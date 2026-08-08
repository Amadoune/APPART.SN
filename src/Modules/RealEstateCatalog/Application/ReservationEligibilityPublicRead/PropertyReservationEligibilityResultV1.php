<?php

namespace Appart\Modules\RealEstateCatalog\Application\ReservationEligibilityPublicRead;

final readonly class PropertyReservationEligibilityResultV1
{
    private function __construct(public PropertyReservationEligibilityStatusV1 $status) {}

    public static function eligible(): self
    {
        return new self(PropertyReservationEligibilityStatusV1::Eligible);
    }

    public static function notEligible(): self
    {
        return new self(PropertyReservationEligibilityStatusV1::NotEligible);
    }

    public static function missing(): self
    {
        return new self(PropertyReservationEligibilityStatusV1::Missing);
    }

    public static function corrupted(): self
    {
        return new self(PropertyReservationEligibilityStatusV1::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(PropertyReservationEligibilityStatusV1::DependencyUnavailable);
    }
}
