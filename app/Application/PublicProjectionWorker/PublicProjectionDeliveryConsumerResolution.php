<?php

namespace App\Application\PublicProjectionWorker;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionRoutedDeliveryConsumerV1;
use App\Application\PublicProjectionDelivery\PublicProjectionDeliveryConsumptionResult;

final readonly class PublicProjectionDeliveryConsumerResolution
{
    private function __construct(
        public PublicProjectionDeliveryConsumer|PublicProjectionRoutedDeliveryConsumerV1|null $consumer,
        public ?PublicProjectionDeliveryMode $mode,
        public ?PublicProjectionDeliveryConsumptionResult $failure,
    ) {}

    public static function found(
        PublicProjectionDeliveryConsumer|PublicProjectionRoutedDeliveryConsumerV1 $consumer,
        PublicProjectionDeliveryMode $mode,
    ): self {
        return new self($consumer, $mode, null);
    }

    public static function unsupportedType(): self
    {
        return new self(null, null, PublicProjectionDeliveryConsumptionResult::UnsupportedEventType);
    }

    public static function unsupportedVersion(): self
    {
        return new self(null, null, PublicProjectionDeliveryConsumptionResult::UnsupportedPayloadVersion);
    }
}
