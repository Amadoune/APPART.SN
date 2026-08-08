<?php

namespace Appart\Modules\ExperienceAcceptance\Infrastructure\Outbox;

use Appart\Modules\ExperienceAcceptance\Application\Delivery\AccessibilityComplianceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\EndToEndReadinessDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\PerformanceReadinessDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ReleaseCandidateDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\ResponsiveComplianceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserAcceptanceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Delivery\UserExperienceDeliveryV1;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxEventId;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessage;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageId;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageStatus;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageType;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

final readonly class ExperienceAcceptanceOutboxMapper
{
    public function fromDelivery(ResponsiveComplianceDeliveryV1|AccessibilityComplianceDeliveryV1|UserExperienceDeliveryV1|EndToEndReadinessDeliveryV1|PerformanceReadinessDeliveryV1|UserAcceptanceDeliveryV1|ReleaseCandidateDeliveryV1 $delivery, DateTimeImmutable $createdAt): ExperienceAcceptanceOutboxMessage
    {
        $type = ExperienceAcceptanceOutboxMessageType::from($delivery->type->value);
        $observedAt = $this->canonicalInstant(new DateTimeImmutable($delivery->payload->observedAt));
        $eventId = new ExperienceAcceptanceOutboxEventId(hash('sha256', implode("\n", ['experience-acceptance-event-id-v1', ExperienceAcceptanceOutboxMessage::OWNER, $type->value, $observedAt])));
        $messageId = new ExperienceAcceptanceOutboxMessageId(hash('sha256', implode("\n", ['experience-acceptance-message-id-v1', ExperienceAcceptanceOutboxMessage::OWNER, $eventId->value])));
        $checksum = $this->checksum($eventId, $type, $delivery->payload->status->value, $observedAt);
        $created = $createdAt->setTimezone(new DateTimeZone('UTC'));

        return new ExperienceAcceptanceOutboxMessage($messageId, $eventId, $type, $delivery->payload->status->value, $observedAt, $checksum, $created, $created, 0, ExperienceAcceptanceOutboxMessageStatus::Pending);
    }

    /** @param array{message_id:string,event_id:string,message_type:string,delivery_status:string,observed_at:string,message_checksum:string,created_at:string,available_at:string,attempts:int|string,technical_status:string} $row */
    public function fromRow(array $row): ExperienceAcceptanceOutboxMessage
    {
        $eventId = new ExperienceAcceptanceOutboxEventId($row['event_id']);
        $type = ExperienceAcceptanceOutboxMessageType::from($row['message_type']);
        $observedAt = $this->canonicalInstant(new DateTimeImmutable($row['observed_at']));
        $checksum = $this->checksum($eventId, $type, $row['delivery_status'], $observedAt);
        if (! hash_equals($row['message_checksum'], $checksum)) {
            throw new RuntimeException('ExperienceAcceptance outbox checksum mismatch.');
        }

        return new ExperienceAcceptanceOutboxMessage(
            new ExperienceAcceptanceOutboxMessageId($row['message_id']),
            $eventId,
            $type,
            $row['delivery_status'],
            $observedAt,
            $checksum,
            new DateTimeImmutable($row['created_at']),
            new DateTimeImmutable($row['available_at']),
            (int) $row['attempts'],
            ExperienceAcceptanceOutboxMessageStatus::from($row['technical_status']),
        );
    }

    public function checksum(ExperienceAcceptanceOutboxEventId $eventId, ExperienceAcceptanceOutboxMessageType $type, string $status, string $observedAt): string
    {
        return hash('sha256', implode("\n", ['experience-acceptance-outbox-v1', ExperienceAcceptanceOutboxMessage::OWNER, (string) ExperienceAcceptanceOutboxMessage::SCHEMA_VERSION, $eventId->value, $type->value, $status, $observedAt]));
    }

    private function canonicalInstant(DateTimeImmutable $instant): string
    {
        return $instant->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
