<?php

namespace App\Application\LeadLifecycleEventTransport;

final readonly class LeadLifecycleTransportEnvelope
{
    public const int VERSION = 1;

    private function __construct(
        public string $messageId,
        public string $messageType,
        public int $transportVersion,
        public LeadLifecycleDeliveryPayload $payload,
        public LeadLifecycleDeliveryMetadata $metadata,
    ) {
        if (preg_match('/^lead-lifecycle-delivery-[0-9a-f]{64}$/', $messageId) !== 1
            || $messageType !== $payload->event->metadata->eventType->value
            || $transportVersion !== self::VERSION
            || $metadata->businessEventId !== $payload->event->payload->eventId->value
            || $metadata->payloadChecksum !== $payload->checksum()) {
            throw new LeadLifecycleEventTransportException('Lead lifecycle transport envelope is inconsistent.');
        }
    }

    public static function wrap(LeadLifecycleDeliveryPayload $payload): self
    {
        return new self(
            'lead-lifecycle-delivery-'.hash('sha256', $payload->fields()['canonicalEvent']),
            $payload->event->metadata->eventType->value,
            self::VERSION,
            $payload,
            LeadLifecycleDeliveryMetadata::fromPayload($payload),
        );
    }
}
