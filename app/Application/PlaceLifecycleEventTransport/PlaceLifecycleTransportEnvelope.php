<?php

namespace App\Application\PlaceLifecycleEventTransport;

final readonly class PlaceLifecycleTransportEnvelope
{
    private function __construct(
        public PlaceLifecycleMessageId $messageId,
        public string $messageType,
        public PlaceLifecycleTransportVersion $transportVersion,
        public PlaceLifecycleDeliveryPayload $payload,
        public PlaceLifecycleDeliveryMetadata $metadata,
    ) {
        $derivedMessageId = PlaceLifecycleMessageId::derive($transportVersion, $payload->transportChecksum());

        if (
            $messageId->value !== $derivedMessageId->value
            || $messageType !== $payload->event->type->value
            || $metadata->eventId !== $payload->event->eventId->value
            || $metadata->payloadChecksum->value !== $payload->transportChecksum()->value
        ) {
            throw new PlaceLifecycleEventTransportException('Place Lifecycle transport envelope is inconsistent.');
        }
    }

    public static function wrap(PlaceLifecycleDeliveryPayload $payload): self
    {
        $version = PlaceLifecycleTransportVersion::V1;

        return new self(
            PlaceLifecycleMessageId::derive($version, $payload->transportChecksum()),
            $payload->event->type->value,
            $version,
            $payload,
            PlaceLifecycleDeliveryMetadata::fromPayload($payload),
        );
    }
}
