<?php

namespace App\Application\MediaItemLifecycleEventTransport;

final readonly class MediaItemLifecycleTransportEnvelope
{
    public const int VERSION = 1;

    private function __construct(
        public string $messageId,
        public string $messageType,
        public int $transportVersion,
        public MediaItemLifecycleDeliveryPayload $payload,
        public MediaItemLifecycleDeliveryMetadata $metadata,
    ) {
        if (preg_match('/^media-item-lifecycle-delivery-[0-9a-f]{64}$/', $messageId) !== 1
            || $messageType !== $payload->event->metadata->eventType->value
            || $transportVersion !== self::VERSION
            || $metadata->businessEventId !== $payload->event->payload->eventId->value
            || $metadata->payloadChecksum !== $payload->checksum()) {
            throw new MediaItemLifecycleEventTransportException('Media item lifecycle transport envelope is inconsistent.');
        }
    }

    public static function wrap(MediaItemLifecycleDeliveryPayload $payload): self
    {
        return new self(
            'media-item-lifecycle-delivery-'.hash('sha256', $payload->fields()['canonicalEvent']),
            $payload->event->metadata->eventType->value,
            self::VERSION,
            $payload,
            MediaItemLifecycleDeliveryMetadata::fromPayload($payload),
        );
    }
}
