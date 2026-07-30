<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleEvent;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use InvalidArgumentException;

final readonly class MediaItemLifecycleEvent
{
    public function __construct(
        public MediaItemLifecycleEventMetadata $metadata,
        public MediaItemLifecycleEventPayload $payload,
    ) {
        $transition = new MediaItemLifecycleTransition($payload->previousState, $payload->currentState, $payload->action);
        $type = (new MediaItemLifecycleEventCatalog)->typeFor($transition);
        $eventId = MediaItemLifecycleEventId::derive(
            $metadata->eventType,
            $metadata->payloadVersion,
            $payload->mediaId,
            $transition,
            $payload->occurredVersion,
        );
        if ($type !== $metadata->eventType
            || $metadata->payloadVersion->value !== $payload->version
            || $eventId->value !== $payload->eventId->value) {
            throw new InvalidArgumentException('Inconsistent media item lifecycle event envelope.');
        }
    }

    /** @return array{eventId:string,eventType:string,payloadVersion:int,payload:array<string,int|string>,metadata:array<string,int|string>} */
    public function canonical(): array
    {
        return [
            'eventId' => $this->payload->eventId->value,
            'eventType' => $this->metadata->eventType->value,
            'payloadVersion' => $this->metadata->payloadVersion->value,
            'payload' => $this->payload->fields(),
            'metadata' => $this->metadata->fields(),
        ];
    }
}
