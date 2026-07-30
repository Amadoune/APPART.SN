<?php

namespace App\Application\ReservationLifecycleEventTransport;

use UnexpectedValueException;

final readonly class ReservationLifecycleTransportEnvelope
{
    public const int VERSION = 1;

    private function __construct(
        public string $messageId,
        public string $messageType,
        public int $transportVersion,
        public ReservationLifecycleDeliveryPayload $payload,
        public ReservationLifecycleDeliveryMetadata $metadata,
    ) {
        if (preg_match('/^reservation-lifecycle-delivery-[0-9a-f]{64}$/', $messageId) !== 1
            || $messageType !== $payload->event->metadata->eventType->value
            || $transportVersion !== self::VERSION
            || $metadata->businessEventId !== $payload->event->payload->eventId->value
            || $metadata->payloadChecksum !== $payload->checksum()) {
            throw new UnexpectedValueException('Reservation lifecycle transport envelope is inconsistent.');
        }
    }

    public static function wrap(ReservationLifecycleDeliveryPayload $payload): self
    {
        $canonicalEvent = $payload->fields()['canonicalEvent'];

        return new self(
            'reservation-lifecycle-delivery-'.hash('sha256', $canonicalEvent),
            $payload->event->metadata->eventType->value,
            self::VERSION,
            $payload,
            ReservationLifecycleDeliveryMetadata::fromPayload($payload),
        );
    }
}
