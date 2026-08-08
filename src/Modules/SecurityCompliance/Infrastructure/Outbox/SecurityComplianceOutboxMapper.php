<?php

namespace Appart\Modules\SecurityCompliance\Infrastructure\Outbox;

use Appart\Modules\SecurityCompliance\Application\Delivery\ComplianceControlDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\IncidentDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\PrivacyPolicyDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecretInventoryDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Delivery\SecurityAuditDeliveryV1;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxEventId;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxMessage;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxMessageId;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxMessageStatus;
use Appart\Modules\SecurityCompliance\Application\Outbox\SecurityComplianceOutboxMessageType;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

final readonly class SecurityComplianceOutboxMapper
{
    public function fromDelivery(SecretInventoryDeliveryV1|SecurityAuditDeliveryV1|IncidentDeliveryV1|PrivacyPolicyDeliveryV1|ComplianceControlDeliveryV1 $delivery, DateTimeImmutable $createdAt): SecurityComplianceOutboxMessage
    {
        $type = SecurityComplianceOutboxMessageType::from($delivery->type->value);
        $observedAt = $this->canonicalInstant(new DateTimeImmutable($delivery->payload->observedAt));
        $eventId = new SecurityComplianceOutboxEventId(hash('sha256', implode("\n", ['security-compliance-event-id-v1', SecurityComplianceOutboxMessage::OWNER, $type->value, $observedAt])));
        $messageId = new SecurityComplianceOutboxMessageId(hash('sha256', implode("\n", ['security-compliance-message-id-v1', SecurityComplianceOutboxMessage::OWNER, $eventId->value])));
        $checksum = $this->checksum($eventId, $type, $delivery->payload->status->value, $observedAt);
        $created = $createdAt->setTimezone(new DateTimeZone('UTC'));

        return new SecurityComplianceOutboxMessage($messageId, $eventId, $type, $delivery->payload->status->value, $observedAt, $checksum, $created, $created, 0, SecurityComplianceOutboxMessageStatus::Pending);
    }

    /** @param array{message_id:string,event_id:string,message_type:string,delivery_status:string,observed_at:string,message_checksum:string,created_at:string,available_at:string,attempts:int|string,technical_status:string} $row */
    public function fromRow(array $row): SecurityComplianceOutboxMessage
    {
        $eventId = new SecurityComplianceOutboxEventId($row['event_id']);
        $type = SecurityComplianceOutboxMessageType::from($row['message_type']);
        $observedAt = $this->canonicalInstant(new DateTimeImmutable($row['observed_at']));
        $checksum = $this->checksum($eventId, $type, $row['delivery_status'], $observedAt);
        if (! hash_equals($row['message_checksum'], $checksum)) {
            throw new RuntimeException('SecurityCompliance outbox checksum mismatch.');
        }

        return new SecurityComplianceOutboxMessage(
            new SecurityComplianceOutboxMessageId($row['message_id']),
            $eventId,
            $type,
            $row['delivery_status'],
            $observedAt,
            $checksum,
            new DateTimeImmutable($row['created_at']),
            new DateTimeImmutable($row['available_at']),
            (int) $row['attempts'],
            SecurityComplianceOutboxMessageStatus::from($row['technical_status']),
        );
    }

    public function checksum(SecurityComplianceOutboxEventId $eventId, SecurityComplianceOutboxMessageType $type, string $status, string $observedAt): string
    {
        return hash('sha256', implode("\n", ['security-compliance-outbox-v1', SecurityComplianceOutboxMessage::OWNER, (string) SecurityComplianceOutboxMessage::SCHEMA_VERSION, $eventId->value, $type->value, $status, $observedAt]));
    }

    private function canonicalInstant(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
