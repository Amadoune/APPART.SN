<?php

namespace App\Application\PublicProjectionDelivery;

use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryPayload;
use DateTimeImmutable;
use InvalidArgumentException;
use ReflectionClass;

final readonly class PublicProjectionDeliveryMessage
{
    public function __construct(
        public PublicProjectionDeliveryMessageId $messageId,
        public PublicProjectionDeliveryIdempotencyKey $idempotencyKey,
        public PublicProjectionDeliveryEventType $eventType,
        public PublicProjectionDeliveryPayloadVersion $payloadVersion,
        public PublicProjectionDeliverySourceModule $sourceModule,
        public PublicProjectionDeliveryAggregateType $aggregateType,
        public PublicProjectionDeliveryAggregateId $aggregateId,
        public PublicProjectionDeliveryOrder $order,
        public DateTimeImmutable $occurredAt,
        public DateTimeImmutable $recordedAt,
        public PublicProjectionDeliveryPayload $payload,
        public ?PublicProjectionDeliveryTraceId $correlationId = null,
        public ?PublicProjectionDeliveryTraceId $causationId = null,
    ) {
        if ($messageId != PublicProjectionDeliveryMessageId::fromIdempotencyKey($idempotencyKey)) {
            throw new InvalidArgumentException('Public Projection Delivery message identity does not match its idempotency key.');
        }
        if (! (new ReflectionClass($payload))->isReadOnly()) {
            throw new InvalidArgumentException('Public Projection Delivery payload must be immutable.');
        }
    }

    public function hasDivergentPayload(self $other): bool
    {
        return $this->idempotencyKey == $other->idempotencyKey && $this->payload->checksum() !== $other->payload->checksum();
    }
}
