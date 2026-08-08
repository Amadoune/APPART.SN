<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime;

final readonly class ReservationAvailabilityOwnerSourceRuntimeDiagnostics
{
    public function __construct(
        public string $runtimeId,
        public string $version,
        public ReservationAvailabilityOwnerSourceRuntimeAvailability $availability,
    ) {}
}
