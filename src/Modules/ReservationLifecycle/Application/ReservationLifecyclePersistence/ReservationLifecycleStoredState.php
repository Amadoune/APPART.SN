<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;

final readonly class ReservationLifecycleStoredState
{
    public function __construct(
        public ReservationId $reservationId,
        public ReservationLifecycleState $state,
        public int $version,
    ) {}
}
