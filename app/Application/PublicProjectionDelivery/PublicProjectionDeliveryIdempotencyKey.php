<?php

namespace App\Application\PublicProjectionDelivery;

final readonly class PublicProjectionDeliveryIdempotencyKey
{
    private function __construct(public string $value) {}

    public static function fromComponents(
        PublicProjectionDeliverySourceModule $sourceModule,
        PublicProjectionDeliveryAggregateType $aggregateType,
        PublicProjectionDeliveryAggregateId $aggregateId,
        int $aggregateVersion,
        PublicProjectionDeliveryEventIndex $eventIndex,
        PublicProjectionDeliveryEventType $eventType,
        PublicProjectionDeliveryPayloadVersion $payloadVersion,
    ): self {
        $segments = [$sourceModule->value, $aggregateType->value, $aggregateId->value, (string) $aggregateVersion, (string) $eventIndex->value, $eventType->value, (string) $payloadVersion->value];

        return new self(implode(':', array_map(static fn (string $segment): string => rawurlencode($segment), $segments)));
    }
}
