<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead;

use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityReadResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSource\ReservationAvailabilityReadStatus;
use Appart\Modules\ReservationLifecycle\Application\ReservationAvailabilityOwnerSourceRuntimeRead\Contract\ReservationAvailabilityOwnerSourceRuntimeReadPolicy;

final readonly class DeterministicReservationAvailabilityOwnerSourceRuntimeReadPolicy implements ReservationAvailabilityOwnerSourceRuntimeReadPolicy
{
    public function reduce(ReservationAvailabilityReadResult $sourceResult): ReservationAvailabilityOwnerSourceRuntimeReadResult
    {
        return match ($sourceResult->status) {
            ReservationAvailabilityReadStatus::Available => ReservationAvailabilityOwnerSourceRuntimeReadResult::allowed(),
            ReservationAvailabilityReadStatus::Conflicting => ReservationAvailabilityOwnerSourceRuntimeReadResult::conflicting(),
            ReservationAvailabilityReadStatus::Missing => ReservationAvailabilityOwnerSourceRuntimeReadResult::missing(),
            ReservationAvailabilityReadStatus::Corrupted => ReservationAvailabilityOwnerSourceRuntimeReadResult::corrupted(),
            ReservationAvailabilityReadStatus::DependencyUnavailable => ReservationAvailabilityOwnerSourceRuntimeReadResult::dependencyUnavailable(),
        };
    }
}
