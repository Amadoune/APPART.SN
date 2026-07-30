<?php

namespace App\Application\MediaIngestionEventOutbox\Contract;

use App\Application\MediaIngestionEventOutbox\MediaIngestionOutboxWriteResult;
use App\Application\MediaIngestionEventRouting\MediaIngestionRoutingDestination;
use App\Application\MediaIngestionEventTransport\MediaIngestionDeliveryMessageV1;

interface MediaIngestionOutboxWriter
{
    /** @param list<MediaIngestionRoutingDestination> $destinations */
    public function append(
        MediaIngestionDeliveryMessageV1 $message,
        array $destinations,
    ): MediaIngestionOutboxWriteResult;
}
