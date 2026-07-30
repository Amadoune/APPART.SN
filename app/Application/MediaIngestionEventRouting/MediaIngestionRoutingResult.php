<?php

namespace App\Application\MediaIngestionEventRouting;

use App\Application\MediaIngestionEventTransport\MediaIngestionDeliveryMessageV1;

final readonly class MediaIngestionRoutingResult
{
    /** @param list<MediaIngestionRoutingDestination> $destinations */
    public function __construct(
        public MediaIngestionDeliveryMessageV1 $message,
        public array $destinations,
        public ?string $diagnostic = null,
    ) {}

    /** @param list<MediaIngestionRoutingDestination> $destinations */
    public static function routed(MediaIngestionDeliveryMessageV1 $message, array $destinations): self
    {
        return new self($message, $destinations);
    }

    public static function rejected(MediaIngestionDeliveryMessageV1 $message): self
    {
        return new self($message, [], 'corrupted_message');
    }
}
