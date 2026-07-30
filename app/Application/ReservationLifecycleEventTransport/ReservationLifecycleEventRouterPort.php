<?php

namespace App\Application\ReservationLifecycleEventTransport;

interface ReservationLifecycleEventRouterPort
{
    public function route(ReservationLifecycleTransportEnvelope $envelope): ReservationLifecycleRoutingResult;
}
