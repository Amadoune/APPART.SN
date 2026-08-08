<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerReader;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\Contract\ReservationAvailabilityOwnerSourceRuntimeReadV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\ReservationAvailabilityOwnerSourceRuntimeReadStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\Contract\ReservationAvailabilityReaderV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityIntentId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityObservedAt;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityResultV1;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilitySubjectId;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityPublicRead\ReservationAvailabilityWindow;

final readonly class OwnerReservationAvailabilityReaderV1 implements ReservationAvailabilityReaderV1
{
    public function __construct(private ReservationAvailabilityOwnerSourceRuntimeReadV1 $runtimeRead) {}

    public function read(
        ReservationAvailabilityIntentId $intentId,
        ReservationAvailabilitySubjectId $subjectId,
        ReservationAvailabilityWindow $window,
        ReservationAvailabilityObservedAt $observedAt,
    ): ReservationAvailabilityResultV1 {
        return match ($this->runtimeRead->read($intentId, $subjectId, $window, $observedAt)->status) {
            ReservationAvailabilityOwnerSourceRuntimeReadStatus::Allowed => ReservationAvailabilityResultV1::available(),
            ReservationAvailabilityOwnerSourceRuntimeReadStatus::Conflicting => ReservationAvailabilityResultV1::conflicting(),
            ReservationAvailabilityOwnerSourceRuntimeReadStatus::Missing => ReservationAvailabilityResultV1::missing(),
            ReservationAvailabilityOwnerSourceRuntimeReadStatus::Corrupted => ReservationAvailabilityResultV1::corrupted(),
            ReservationAvailabilityOwnerSourceRuntimeReadStatus::DependencyUnavailable => ReservationAvailabilityResultV1::dependencyUnavailable(),
        };
    }
}
