<?php

namespace App\Application\LeadLifecycleEventTransport;

interface LeadLifecycleEventRouter
{
    public function route(LeadLifecycleTransportEnvelope $envelope): LeadLifecycleEventRoutingResult;
}
