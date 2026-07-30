<?php

namespace App\Application\MediaIngestionEventDelivery;

use App\Application\MediaIngestionEventRouting\MediaIngestionRoutingDestination;

final readonly class MediaIngestionDeliveryObservation
{
    public function __construct(
        public string $messageId,
        public MediaIngestionRoutingDestination $destination,
        public MediaIngestionDeliveryOutcome $outcome,
        public int $attempt,
    ) {
        if ($attempt < 1) {
            throw new \InvalidArgumentException('Attempt must be positive.');
        }
    }
}
