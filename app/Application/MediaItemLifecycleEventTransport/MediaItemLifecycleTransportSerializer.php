<?php

namespace App\Application\MediaItemLifecycleEventTransport;

final readonly class MediaItemLifecycleTransportSerializer
{
    public function serialize(MediaItemLifecycleTransportEnvelope $envelope): string
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
