<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\Contract;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadDiagnostics;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;

interface ReservationAvailabilityOwnerSourceRuntimeReadV1
{
    public function read(
        ReservationAvailabilityIntentId $intentId,
        ReservationAvailabilitySubjectId $subjectId,
        ReservationAvailabilityWindow $window,
        ReservationAvailabilityObservedAt $observedAt,
    ): ReservationAvailabilityOwnerSourceRuntimeReadResult;

    public function diagnostics(): ReservationAvailabilityOwnerSourceRuntimeReadDiagnostics;
}
