<?php

namespace App\Application\ReservationLifecycleEventTransport;

final readonly class ReservationLifecycleRoutingResult
{
    public function __construct(public ReservationLifecycleRoutingStatus $status) {}
}
