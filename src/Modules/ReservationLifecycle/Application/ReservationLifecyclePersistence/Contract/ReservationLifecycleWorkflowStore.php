<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\Contract;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleState;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleTransition;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecyclePersistenceReadResult;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationLifecyclePersistenceWriteResult;

interface ReservationLifecycleWorkflowStore
{
    public function initialize(ReservationId $reservationId, ReservationLifecycleState $state): ReservationLifecyclePersistenceWriteResult;

    public function append(ReservationId $reservationId, ReservationLifecycleTransition $transition, int $version): ReservationLifecyclePersistenceWriteResult;

    public function read(ReservationId $reservationId): ReservationLifecyclePersistenceReadResult;
}
