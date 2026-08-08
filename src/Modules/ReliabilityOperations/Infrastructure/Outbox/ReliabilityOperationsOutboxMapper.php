<?php

namespace Appart\Modules\ReliabilityOperations\Infrastructure\Outbox;

use Appart\Modules\ReliabilityOperations\Application\Delivery\AlertingDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\CapacityPlanningDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ContinuityDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\MaintenanceOperationsDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ObservabilityDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\OperationalReadinessDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Delivery\ServiceHealthDeliveryV1;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxEventId;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxMessage;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxMessageId;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxMessageStatus;
use Appart\Modules\ReliabilityOperations\Application\Outbox\ReliabilityOperationsOutboxMessageType;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

final readonly class ReliabilityOperationsOutboxMapper
{
    public function fromDelivery(ObservabilityDeliveryV1|ServiceHealthDeliveryV1|AlertingDeliveryV1|MaintenanceOperationsDeliveryV1|ContinuityDeliveryV1|CapacityPlanningDeliveryV1|OperationalReadinessDeliveryV1 $delivery, DateTimeImmutable $createdAt): ReliabilityOperationsOutboxMessage
    {
        $type = ReliabilityOperationsOutboxMessageType::from($delivery->type->value);
        $observedAt = $this->canonicalInstant(new DateTimeImmutable($delivery->payload->observedAt));
        $eventId = new ReliabilityOperationsOutboxEventId(hash('sha256', implode("\n", ['reliability-operations-event-id-v1', ReliabilityOperationsOutboxMessage::OWNER, $type->value, $observedAt])));
        $messageId = new ReliabilityOperationsOutboxMessageId(hash('sha256', implode("\n", ['reliability-operations-message-id-v1', ReliabilityOperationsOutboxMessage::OWNER, $eventId->value])));
        $checksum = $this->checksum($eventId, $type, $delivery->payload->status->value, $observedAt);
        $created = $createdAt->setTimezone(new DateTimeZone('UTC'));

        return new ReliabilityOperationsOutboxMessage($messageId, $eventId, $type, $delivery->payload->status->value, $observedAt, $checksum, $created, $created, 0, ReliabilityOperationsOutboxMessageStatus::Pending);
    }

    /** @param array{message_id:string,event_id:string,message_type:string,delivery_status:string,observed_at:string,message_checksum:string,created_at:string,available_at:string,attempts:int|string,technical_status:string} $row */
    public function fromRow(array $row): ReliabilityOperationsOutboxMessage
    {
        $eventId = new ReliabilityOperationsOutboxEventId($row['event_id']);
        $type = ReliabilityOperationsOutboxMessageType::from($row['message_type']);
        $observedAt = $this->canonicalInstant(new DateTimeImmutable($row['observed_at']));
        $checksum = $this->checksum($eventId, $type, $row['delivery_status'], $observedAt);
        if (! hash_equals($row['message_checksum'], $checksum)) {
            throw new RuntimeException('ReliabilityOperations outbox checksum mismatch.');
        }

        return new ReliabilityOperationsOutboxMessage(
            new ReliabilityOperationsOutboxMessageId($row['message_id']),
            $eventId,
            $type,
            $row['delivery_status'],
            $observedAt,
            $checksum,
            new DateTimeImmutable($row['created_at']),
            new DateTimeImmutable($row['available_at']),
            (int) $row['attempts'],
            ReliabilityOperationsOutboxMessageStatus::from($row['technical_status']),
        );
    }

    public function checksum(ReliabilityOperationsOutboxEventId $eventId, ReliabilityOperationsOutboxMessageType $type, string $status, string $observedAt): string
    {
        return hash('sha256', implode("\n", ['reliability-operations-outbox-v1', ReliabilityOperationsOutboxMessage::OWNER, (string) ReliabilityOperationsOutboxMessage::SCHEMA_VERSION, $eventId->value, $type->value, $status, $observedAt]));
    }

    private function canonicalInstant(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
