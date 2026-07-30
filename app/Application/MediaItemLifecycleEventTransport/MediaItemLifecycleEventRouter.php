<?php

namespace App\Application\MediaItemLifecycleEventTransport;

interface MediaItemLifecycleEventRouter
{
    public function route(MediaItemLifecycleTransportEnvelope $envelope): MediaItemLifecycleEventRoutingResult;
}
