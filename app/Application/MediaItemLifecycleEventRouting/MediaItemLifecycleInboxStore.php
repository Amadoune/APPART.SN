<?php

namespace App\Application\MediaItemLifecycleEventRouting;

use App\Application\MediaItemLifecycleEventTransport\MediaItemLifecycleTransportEnvelope;

interface MediaItemLifecycleInboxStore
{
    public function store(MediaItemLifecycleTransportEnvelope $envelope): MediaItemLifecycleInboxStoreResult;
}
