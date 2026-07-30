<?php

namespace App\Application\AdministrativeActionLifecycleEventTransport;

interface AdministrativeActionLifecycleEventRouter
{
    public function route(AdministrativeActionLifecycleTransportEnvelope $envelope): AdministrativeActionLifecycleEventRoutingResult;
}
