<?php

namespace App\Application\PlaceLifecycleEventTransport;

final readonly class PlaceLifecycleDeliveryMetadata
{
    public const string SOURCE = 'PlaceLifecycle';

    private function __construct(
        public string $source,
        public string $eventId,
        public PlaceLifecycleTransportChecksum $payloadChecksum,
    ) {
        if ($source !== self::SOURCE || preg_match('/^[a-f0-9]{64}$/', $eventId) !== 1) {
            throw new PlaceLifecycleEventTransportException('Place Lifecycle delivery metadata is invalid.');
        }
    }

    public static function fromPayload(PlaceLifecycleDeliveryPayload $payload): self
    {
        return new self(self::SOURCE, $payload->event->eventId->value, $payload->transportChecksum());
    }

    /** @return array{source:string,eventId:string,payloadChecksum:string} */
    public function fields(): array
    {
        return [
            'source' => $this->source,
            'eventId' => $this->eventId,
            'payloadChecksum' => $this->payloadChecksum->value,
        ];
    }
}
