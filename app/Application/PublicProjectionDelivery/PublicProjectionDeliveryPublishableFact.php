<?php

namespace App\Application\PublicProjectionDelivery;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use DateTimeImmutable;

final readonly class PublicProjectionDeliveryPublishableFact
{
    public function __construct(
        public PublicProjectionDeliveryEventType $eventType,
        public PublicProjectionDeliveryPayloadVersion $payloadVersion,
        public PublicProjectionDeliverySourceModule $sourceModule,
        public PublicProjectionDeliveryAggregateType $aggregateType,
        public PublicProjectionDeliveryAggregateId $aggregateId,
        public PublicProjectionDeliveryOrder $order,
        public DateTimeImmutable $occurredAt,
        public PublicProjectionDeliveryPayload $payload,
        public ?PublicProjectionDeliveryTraceId $correlationId = null,
        public ?PublicProjectionDeliveryTraceId $causationId = null,
    ) {}
}
