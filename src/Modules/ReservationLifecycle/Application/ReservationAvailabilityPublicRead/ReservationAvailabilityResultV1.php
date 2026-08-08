<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead;

final readonly class ReservationAvailabilityResultV1
{
    private function __construct(public ReservationAvailabilityStatusV1 $status) {}

    public static function available(): self
    {
        return new self(ReservationAvailabilityStatusV1::Available);
    }

    public static function conflicting(): self
    {
        return new self(ReservationAvailabilityStatusV1::Conflicting);
    }

    public static function missing(): self
    {
        return new self(ReservationAvailabilityStatusV1::Missing);
    }

    public static function corrupted(): self
    {
        return new self(ReservationAvailabilityStatusV1::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ReservationAvailabilityStatusV1::DependencyUnavailable);
    }
}
