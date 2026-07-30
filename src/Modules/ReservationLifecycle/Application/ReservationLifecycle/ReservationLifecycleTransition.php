<?php

namespace Appart\Modules\ReservationLifecycle\Application\ReservationLifecycle;

final readonly class ReservationLifecycleTransition
{
    public function __construct(
        public ReservationLifecycleState $from,
        public ReservationLifecycleState $to,
        public ReservationLifecycleAction $action,
    ) {}
}
