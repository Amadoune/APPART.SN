<?php

namespace App\Application\ProfessionalStatusEventTransport;

final readonly class ProfessionalStatusTransportEnvelope
{
    public const int VERSION = 1;

    private function __construct(
        public string $messageId,
        public string $messageType,
        public int $transportVersion,
        public ProfessionalStatusDeliveryPayload $payload,
        public ProfessionalStatusDeliveryMetadata $metadata,
    ) {
        if (preg_match('/^professional-status-delivery-[0-9a-f]{64}$/', $messageId) !== 1
            || $messageType !== $payload->event->metadata->eventType->value
            || $transportVersion !== self::VERSION
            || $metadata->businessEventId !== $payload->event->payload->eventId->value
            || $metadata->payloadChecksum !== $payload->checksum()) {
            throw new ProfessionalStatusEventTransportException('Professional status transport envelope is inconsistent.');
        }
    }

    public static function wrap(ProfessionalStatusDeliveryPayload $payload): self
    {
        return new self(
            'professional-status-delivery-'.hash('sha256', $payload->fields()['canonicalEvent']),
            $payload->event->metadata->eventType->value,
            self::VERSION,
            $payload,
            ProfessionalStatusDeliveryMetadata::fromPayload($payload),
        );
    }
}
