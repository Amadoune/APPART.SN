<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead;

final readonly class ReservationAvailabilityOwnerSourceRuntimeReadResult
{
    private function __construct(public ReservationAvailabilityOwnerSourceRuntimeReadStatus $status) {}

    public static function allowed(): self
    {
        return new self(ReservationAvailabilityOwnerSourceRuntimeReadStatus::Allowed);
    }

    public static function conflicting(): self
    {
        return new self(ReservationAvailabilityOwnerSourceRuntimeReadStatus::Conflicting);
    }

    public static function missing(): self
    {
        return new self(ReservationAvailabilityOwnerSourceRuntimeReadStatus::Missing);
    }

    public static function corrupted(): self
    {
        return new self(ReservationAvailabilityOwnerSourceRuntimeReadStatus::Corrupted);
    }

    public static function dependencyUnavailable(): self
    {
        return new self(ReservationAvailabilityOwnerSourceRuntimeReadStatus::DependencyUnavailable);
    }
}
