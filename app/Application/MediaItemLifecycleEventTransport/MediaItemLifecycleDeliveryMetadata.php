<?php

namespace App\Application\MediaItemLifecycleEventTransport;

final readonly class MediaItemLifecycleDeliveryMetadata
{
    public const string SOURCE = 'MediaItemLifecycle';

    private function __construct(public string $source, public string $businessEventId, public string $payloadChecksum)
    {
        if ($source !== self::SOURCE || preg_match('/^[0-9a-f]{64}$/', $businessEventId) !== 1 || preg_match('/^[0-9a-f]{64}$/', $payloadChecksum) !== 1) {
            throw new MediaItemLifecycleEventTransportException('Media item lifecycle delivery metadata is invalid.');
        }
    }

    public static function fromPayload(MediaItemLifecycleDeliveryPayload $payload): self
    {
        return new self(self::SOURCE, $payload->event->payload->eventId->value, $payload->checksum());
    }

    /** @return array{source:string,businessEventId:string,payloadChecksum:string} */
    public function fields(): array
    {
        return ['source' => $this->source, 'businessEventId' => $this->businessEventId, 'payloadChecksum' => $this->payloadChecksum];
    }
}
