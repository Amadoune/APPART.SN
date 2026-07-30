<?php

namespace App\Application\PlaceLifecycleEventRouting;

use App\Application\PlaceLifecycleEventTransport\PlaceLifecycleTransportEnvelope;

interface PlaceLifecycleInboxStore
{
    public function store(PlaceLifecycleTransportEnvelope $envelope): PlaceLifecycleInboxStoreResult;
}
