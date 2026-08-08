<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\Contract\ReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntime\Contract\ReservationAvailabilityOwnerSourceRuntimeV1;

final readonly class DeterministicReservationAvailabilityOwnerSourceRuntimeV1 implements ReservationAvailabilityOwnerSourceRuntimeV1
{
    private const RUNTIME_ID = 'reservation-lifecycle.availability-owner-source';

    private const VERSION = 'reservation-availability-owner-source-runtime-v1';

    public function __construct(private ReservationAvailabilityOwnerSourceRuntimeAvailabilityPolicy $availabilityPolicy) {}

    public function availability(): ReservationAvailabilityOwnerSourceRuntimeAvailability
    {
        return $this->availabilityPolicy->inspect();
    }

    public function diagnostics(): ReservationAvailabilityOwnerSourceRuntimeDiagnostics
    {
        return new ReservationAvailabilityOwnerSourceRuntimeDiagnostics(
            self::RUNTIME_ID,
            self::VERSION,
            $this->availability(),
        );
    }
}
