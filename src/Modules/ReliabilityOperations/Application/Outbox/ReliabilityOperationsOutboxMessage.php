<?php

namespace Appart\Modules\ReliabilityOperations\Application\Outbox;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class ReliabilityOperationsOutboxMessage
{
    public const OWNER = 'ReliabilityOperations';

    public const SCHEMA_VERSION = 1;

    public function __construct(
        public ReliabilityOperationsOutboxMessageId $messageId,
        public ReliabilityOperationsOutboxEventId $eventId,
        public ReliabilityOperationsOutboxMessageType $type,
        public string $deliveryStatus,
        public string $observedAt,
        public string $checksum,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $availableAt,
        public int $attempts,
        public ReliabilityOperationsOutboxMessageStatus $status,
    ) {
        if (preg_match('/^[0-9a-f]{64}$/', $checksum) !== 1 || $attempts < 0 || $attempts > 10) {
            throw new InvalidArgumentException('Invalid ReliabilityOperations outbox message.');
        }
    }

    /** @return array{type:string,status:string,observedAt:string} */
    public function payload(): array
    {
        return ['type' => $this->type->value, 'status' => $this->deliveryStatus, 'observedAt' => $this->observedAt];
    }
}
