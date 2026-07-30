<?php

namespace App\Application\LeadLifecycleEventRouting;

use App\Application\LeadLifecycleEventTransport\LeadLifecycleTransportEnvelope;

interface LeadLifecycleInboxStore
{
    public function store(LeadLifecycleTransportEnvelope $envelope): LeadLifecycleInboxStoreResult;
}
