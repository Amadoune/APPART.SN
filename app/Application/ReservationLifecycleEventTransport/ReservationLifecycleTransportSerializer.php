<?php

namespace App\Application\ReservationLifecycleEventTransport;

final readonly class ReservationLifecycleTransportSerializer
{
    public function serialize(ReservationLifecycleTransportEnvelope $envelope): string
    {
        return json_encode([
            'messageId' => $envelope->messageId,
            'messageType' => $envelope->messageType,
            'transportVersion' => $envelope->transportVersion,
            'payload' => $envelope->payload->fields(),
            'metadata' => $envelope->metadata->fields(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
