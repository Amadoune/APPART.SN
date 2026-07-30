<?php

namespace Appart\Modules\RealEstateCatalog\Application\PropertyLifecycle\Event;

use InvalidArgumentException;

final readonly class PropertyLifecycleEvent
{
    public function __construct(
        public PropertyLifecycleEventId $eventId,
        public PropertyLifecycleEventType $type,
        public PropertyLifecycleEventPayloadVersion $payloadVersion,
        public PropertyLifecycleEventPayload $payload,
        public PropertyLifecycleEventMetadata $metadata,
    ) {
        if ($eventId != PropertyLifecycleEventId::derive($type, $payloadVersion, $payload)) {
            throw new InvalidArgumentException('Property lifecycle event identity is not deterministic.');
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
