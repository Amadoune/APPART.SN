<?php

namespace App\Application\AdministrativeActionLifecycleEventTransport;

final readonly class AdministrativeActionLifecycleDeliveryMetadata
{
    private function __construct(
        public string $source,
        public string $businessEventId,
        public string $payloadChecksum,
    ) {}

    public static function fromPayload(AdministrativeActionLifecycleDeliveryPayload $payload): self
    {
        return new self(
            'AdministrationAudit',
            $payload->event->payload->eventId->value,
            $payload->checksum(),
        );
    }

    /** @return array{source:string,businessEventId:string,payloadChecksum:string} */
    public function fields(): array
    {
        return [
            'source' => $this->source,
            'businessEventId' => $this->businessEventId,
            'payloadChecksum' => $this->payloadChecksum,
        ];
    }
}
