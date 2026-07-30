<?php

namespace App\Application\PublicProjectionWorker;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionRoutedDeliveryConsumerV1;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryEventType;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryPayloadVersion;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;

final readonly class PublicProjectionDeliveryConsumerRegistration
{
    public function __construct(
        public PublicProjectionOutboxConsumerId $consumerId,
        public PublicProjectionDeliveryEventType $eventType,
        public PublicProjectionDeliveryPayloadVersion $payloadVersion,
        public PublicProjectionDeliveryConsumer|PublicProjectionRoutedDeliveryConsumerV1 $consumer,
        public PublicProjectionDeliveryMode $mode = PublicProjectionDeliveryMode::Legacy,
    ) {
        if ($mode === PublicProjectionDeliveryMode::Legacy && ! $consumer instanceof PublicProjectionDeliveryConsumer) {
            throw new \InvalidArgumentException('Legacy delivery registrations require a legacy consumer.');
        }
        if ($mode === PublicProjectionDeliveryMode::RoutedV1 && ! $consumer instanceof PublicProjectionRoutedDeliveryConsumerV1) {
            throw new \InvalidArgumentException('Routed V1 registrations require a routed consumer.');
        }
    }
}
