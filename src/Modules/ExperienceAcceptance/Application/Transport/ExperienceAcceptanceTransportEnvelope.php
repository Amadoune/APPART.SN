<?php

namespace Appart\Modules\ExperienceAcceptance\Application\Transport;

use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxEventId;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessage;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageId;
use Appart\Modules\ExperienceAcceptance\Application\Outbox\ExperienceAcceptanceOutboxMessageType;

final readonly class ExperienceAcceptanceTransportEnvelope
{
    public function __construct(
        public ExperienceAcceptanceOutboxMessageId $messageId,
        public ExperienceAcceptanceOutboxEventId $eventId,
        public ExperienceAcceptanceOutboxMessageType $type,
        public string $status,
        public string $observedAt,
        public string $checksum,
    ) {
        if (preg_match('/^[0-9a-f]{64}$/', $checksum) !== 1) {
            throw new ExperienceAcceptanceTransportException('Invalid ExperienceAcceptance transport checksum.');
        }
    }

    public static function fromMessage(ExperienceAcceptanceOutboxMessage $message): self
    {
        return new self($message->messageId, $message->eventId, $message->type, $message->deliveryStatus, $message->observedAt, $message->checksum);
    }

    /** @return array{messageId:string,eventId:string,type:string,status:string,observedAt:string,checksum:string} */
    public function canonical(): array
    {
        return ['messageId' => $this->messageId->value, 'eventId' => $this->eventId->value, 'type' => $this->type->value, 'status' => $this->status, 'observedAt' => $this->observedAt, 'checksum' => $this->checksum];
    }
}
