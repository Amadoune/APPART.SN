<?php

namespace App\Application\ProfessionalStatusEventTransport;

final readonly class ProfessionalStatusDeliveryMetadata
{
    public const string SOURCE = 'ProfessionalStatus';

    private function __construct(public string $source, public string $businessEventId, public string $payloadChecksum)
    {
        if ($source !== self::SOURCE || preg_match('/^[0-9a-f]{64}$/', $businessEventId) !== 1 || preg_match('/^[0-9a-f]{64}$/', $payloadChecksum) !== 1) {
            throw new ProfessionalStatusEventTransportException('Professional status delivery metadata is invalid.');
        }
    }

    public static function fromPayload(ProfessionalStatusDeliveryPayload $payload): self
    {
        return new self(self::SOURCE, $payload->event->payload->eventId->value, $payload->checksum());
    }

    /** @return array{source:string,businessEventId:string,payloadChecksum:string} */
    public function fields(): array
    {
        return ['source' => $this->source, 'businessEventId' => $this->businessEventId, 'payloadChecksum' => $this->payloadChecksum];
    }
}
