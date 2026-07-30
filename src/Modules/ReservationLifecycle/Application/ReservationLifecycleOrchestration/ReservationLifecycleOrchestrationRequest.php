<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycleOrchestration;

use Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle\ReservationLifecycleAction;
use Appart\Modules\ReservationLifecycle\Application\ReservationLifecyclePersistence\ReservationId;
use InvalidArgumentException;

final readonly class ReservationLifecycleOrchestrationRequest
{
    public function __construct(
        public ReservationId $reservationId,
        public ReservationLifecycleAction $action,
        public int $expectedVersion,
    ) {
        if ($expectedVersion < 1) {
            throw new InvalidArgumentException('The expected reservation lifecycle version must be positive.');
        }
    }
}
