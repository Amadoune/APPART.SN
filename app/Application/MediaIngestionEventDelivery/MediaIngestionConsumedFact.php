<?php

namespace App\Application\MediaIngestionEventDelivery;

use App\Application\MediaIngestionEventRouting\MediaIngestionRoutingDestination;
use Appart\Modules\Media\Application\MediaIngestionEvent\MediaIngestionEventV1;

final readonly class MediaIngestionConsumedFact
{
    public function __construct(
        public string $messageId,
        public string $payloadChecksum,
        public MediaIngestionRoutingDestination $destination,
        public MediaIngestionEventV1 $event,
    ) {}
}
