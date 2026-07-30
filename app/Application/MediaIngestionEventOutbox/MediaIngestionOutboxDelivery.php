<?php

namespace App\Application\MediaIngestionEventOutbox;

use App\Application\MediaIngestionEventRouting\MediaIngestionRoutingDestination;
use App\Application\MediaIngestionEventTransport\MediaIngestionDeliveryMessageV1;

final readonly class MediaIngestionOutboxDelivery
{
    public function __construct(
        public MediaIngestionDeliveryMessageV1 $message,
        public MediaIngestionRoutingDestination $destination,
        public int $attempt,
        public string $claimOwner,
    ) {}
}
