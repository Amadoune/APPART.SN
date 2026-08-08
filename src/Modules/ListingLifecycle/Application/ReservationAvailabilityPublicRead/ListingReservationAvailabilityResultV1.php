<?php

namespace Appart\Modules\ListingLifecycle\Application\ReservationAvailabilityPublicRead;

final readonly class ListingReservationAvailabilityResultV1
{
    private function __construct(public ListingReservationAvailabilityStatusV1 $status) {}

    public static function reservable(): self
    {
        return new self(ListingReservationAvailabilityStatusV1::Reservable);
    }

    public static function notReservable(): self
    {
        return new self(ListingReservationAvailabilityStatusV1::NotReservable);
    }

    public static function missing(): self
    {
        return new self(ListingReservationAvailabilityStatusV1::Missing);
    }

    public static function corrupted(): self
    {
        return new self(ListingReservationAvailabilityStatusV1::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ListingReservationAvailabilityStatusV1::DependencyUnavailable);
    }
}
