<?php

namespace App\Application\PublicProjectionReconciliation;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;

final readonly class PublicProjectionReconciliationObservation
{
    public function __construct(
        public PublicProjectionOutboxConsumerId $consumerId,
        public PublicProjectionDeliverySourceModule $sourceModule,
        public PublicProjectionDeliveryAggregateId $aggregateId,
        public ?PublicProjectionDeliveryOrder $progress,
        public ?PublicProjectionDeliveryOrder $outboxHighWatermark,
        public ?PublicProjectionDeliveryOrder $projectionHighWatermark,
        public ?PublicProjectionDeliveryMessageId $expectedMessageId = null,
        public bool $messageMissing = false,
        public bool $sequenceGap = false,
        public bool $blockedBySourceReadiness = false,
        public bool $blockedBySequenceGap = false,
    ) {}
}
