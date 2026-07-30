<?php

namespace App\Application\ReservationLifecycleEventRouting;

use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleRoutingResult;
use App\Application\ReservationLifecycleEventTransport\ReservationLifecycleTransportEnvelope;

interface ReservationLifecycleInboxStore
{
    public function store(ReservationLifecycleTransportEnvelope $envelope): ReservationLifecycleRoutingResult;
}
