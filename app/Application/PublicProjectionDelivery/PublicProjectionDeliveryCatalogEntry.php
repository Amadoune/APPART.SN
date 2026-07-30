<?php

namespace App\Application\PublicProjectionDelivery;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;

final readonly class PublicProjectionDeliveryCatalogEntry
{
    /** @param class-string<PublicProjectionDeliveryPayload> $payloadClass */
    public function __construct(
        public PublicProjectionDeliveryEventType $eventType,
        public PublicProjectionDeliverySourceModule $sourceModule,
        public PublicProjectionDeliveryAggregateType $aggregateType,
        public PublicProjectionDeliveryPayloadVersion $payloadVersion,
        public string $payloadClass,
        public bool $deprecated = false,
    ) {}
}
