<?php

namespace Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\Event;

use InvalidArgumentException;

final readonly class ListingPublicationEvent
{
    public function __construct(
        public ListingPublicationEventId $eventId,
        public ListingPublicationEventType $type,
        public ListingPublicationEventPayloadVersion $payloadVersion,
        public ListingPublicationEventPayload $payload,
        public ListingPublicationEventMetadata $metadata,
    ) {
        if ($eventId != ListingPublicationEventId::derive($type, $payloadVersion, $payload)) {
            throw new InvalidArgumentException('Listing publication event identity is not deterministic.');
        }
    }

    /** @return array{eventId:string,eventType:string,payloadVersion:int,payload:array<string,int|string>,metadata:array{occurredAt:string,recordedAt:string}} */
    public function fields(): array
    {
        return [
            'eventId' => $this->eventId->value,
            'eventType' => $this->type->value,
            'payloadVersion' => $this->payloadVersion->value,
            'payload' => $this->payload->fields(),
            'metadata' => $this->metadata->fields(),
        ];
    }
}
