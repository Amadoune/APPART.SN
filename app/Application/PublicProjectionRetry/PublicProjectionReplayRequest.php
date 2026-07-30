<?php

namespace App\Application\PublicProjectionRetry;

use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryAggregateId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryMessageId;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryOrder;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliverySourceModule;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use InvalidArgumentException;

final readonly class PublicProjectionReplayRequest
{
    public function __construct(
        public PublicProjectionReplayScope $scope,
        public PublicProjectionOutboxConsumerId $consumerId,
        public PublicProjectionReplayAuthorization $authorization,
        public ?PublicProjectionDeliveryMessageId $messageId = null,
        public ?PublicProjectionDeliverySourceModule $sourceModule = null,
        public ?PublicProjectionDeliveryAggregateId $aggregateId = null,
        public ?PublicProjectionDeliveryOrder $fromExclusive = null,
        public ?PublicProjectionDeliveryOrder $toInclusive = null,
    ) {
        $valid = match ($scope) {
            PublicProjectionReplayScope::Message => $messageId !== null,
            PublicProjectionReplayScope::Aggregate => $sourceModule !== null && $aggregateId !== null,
            PublicProjectionReplayScope::Module => $sourceModule !== null && $aggregateId === null,
            PublicProjectionReplayScope::Range => $sourceModule !== null && $aggregateId !== null && $toInclusive !== null,
            PublicProjectionReplayScope::HighWatermark => $sourceModule !== null && $toInclusive !== null,
        };
        if (! $valid) {
            throw new InvalidArgumentException('Replay request does not satisfy its explicit scope.');
        }
    }
}
