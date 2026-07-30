<?php

namespace App\Application\PublicProjectionDelivery;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryMessageFactory;
use DateTimeImmutable;

final readonly class PublicProjectionDeliveryCatalogMessageFactory implements PublicProjectionDeliveryMessageFactory
{
    public function __construct(private PublicProjectionDeliveryEventCatalog $catalog) {}

    public function create(PublicProjectionDeliveryPublishableFact $fact, DateTimeImmutable $recordedAt): PublicProjectionDeliveryMessage
    {
        if (! $this->catalog->accepts($fact->eventType, $fact->payloadVersion, $fact->sourceModule, $fact->aggregateType, $fact->payload)) {
            throw new PublicProjectionDeliveryUnsupportedFact('The fact is not explicitly listed in the Public Projection Delivery catalog.');
        }

        $key = PublicProjectionDeliveryIdempotencyKey::fromComponents($fact->sourceModule, $fact->aggregateType, $fact->aggregateId, $fact->order->aggregateVersion, $fact->order->eventIndex, $fact->eventType, $fact->payloadVersion);

        return new PublicProjectionDeliveryMessage(PublicProjectionDeliveryMessageId::fromIdempotencyKey($key), $key, $fact->eventType, $fact->payloadVersion, $fact->sourceModule, $fact->aggregateType, $fact->aggregateId, $fact->order, $fact->occurredAt, $recordedAt, $fact->payload, $fact->correlationId, $fact->causationId);
    }
}
