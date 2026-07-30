<?php

namespace App\Application\PublicProjectionOutbox;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;

final readonly class PublicProjectionOutboxCursorIdentity
{
    public function __construct(
        public PublicProjectionOutboxConsumerId $consumerId,
        public PublicProjectionDeliverySourceModule $sourceModule,
        public PublicProjectionDeliveryAggregateType $aggregateType,
        public PublicProjectionDeliveryAggregateId $aggregateId,
    ) {}

    public function value(): string
    {
        return implode(':', array_map('rawurlencode', [$this->consumerId->value, $this->sourceModule->value, $this->aggregateType->value, $this->aggregateId->value]));
    }
}
