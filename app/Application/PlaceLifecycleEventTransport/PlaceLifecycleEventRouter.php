<?php

namespace App\Application\PlaceLifecycleEventTransport;

interface PlaceLifecycleEventRouter
{
    public function route(PlaceLifecycleTransportEnvelope $envelope): PlaceLifecycleEventRoutingResult;
}
