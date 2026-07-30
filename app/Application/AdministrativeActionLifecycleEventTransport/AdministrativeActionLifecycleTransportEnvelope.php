<?php

namespace App\Application\AdministrativeActionLifecycleEventTransport;

final readonly class AdministrativeActionLifecycleTransportEnvelope
{
    public const int VERSION = 1;

    private function __construct(
        public string $messageId,
        public string $messageType,
        public int $transportVersion,
        public AdministrativeActionLifecycleDeliveryPayload $payload,
        public AdministrativeActionLifecycleDeliveryMetadata $metadata,
    ) {
        if (preg_match('/^administrative-action-lifecycle-delivery-[0-9a-f]{64}$/', $messageId) !== 1
            || $messageType !== $payload->event->metadata->eventType->value
            || $transportVersion !== self::VERSION
            || $metadata->businessEventId !== $payload->event->payload->eventId->value
            || $metadata->payloadChecksum !== $payload->checksum()) {
            throw new AdministrativeActionLifecycleEventTransportException('Administrative Action Lifecycle transport envelope is inconsistent.');
        }
    }

    public static function wrap(AdministrativeActionLifecycleDeliveryPayload $payload): self
    {
        return new self(
            'administrative-action-lifecycle-delivery-'.hash('sha256', $payload->fields()['canonicalEvent']),
            $payload->event->metadata->eventType->value,
            self::VERSION,
            $payload,
            AdministrativeActionLifecycleDeliveryMetadata::fromPayload($payload),
        );
    }
}
