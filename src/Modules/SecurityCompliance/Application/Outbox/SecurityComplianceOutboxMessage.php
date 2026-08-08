<?php

namespace Appart\Modules\SecurityCompliance\Application\Outbox;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class SecurityComplianceOutboxMessage
{
    public const OWNER = 'SecurityCompliance';

    public const SCHEMA_VERSION = 1;

    public function __construct(
        public SecurityComplianceOutboxMessageId $messageId,
        public SecurityComplianceOutboxEventId $eventId,
        public SecurityComplianceOutboxMessageType $type,
        public string $deliveryStatus,
        public string $observedAt,
        public string $checksum,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $availableAt,
        public int $attempts,
        public SecurityComplianceOutboxMessageStatus $status,
    ) {
        if (preg_match('/^[0-9a-f]{64}$/', $checksum) !== 1 || $attempts < 0 || $attempts > 10) {
            throw new InvalidArgumentException('Invalid SecurityCompliance outbox message.');
        }
    }

    /** @return array{type:string,status:string,observedAt:string} */
    public function payload(): array
    {
        return ['type' => $this->type->value, 'status' => $this->deliveryStatus, 'observedAt' => $this->observedAt];
    }
}
