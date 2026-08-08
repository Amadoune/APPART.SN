<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead;

final readonly class ReservationAvailabilityOwnerSourceRuntimeReadDiagnostics
{
    public function __construct(
        public string $runtimeReadId,
        public string $version,
        public ReservationAvailabilityOwnerSourceRuntimeReadAvailability $availability,
    ) {}
}
