<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleEvent;

use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use InvalidArgumentException;

final readonly class MediaItemLifecycleEventMetadata
{
    public function __construct(
        public MediaItemLifecycleEventType $eventType,
        public MediaItemLifecycleEventPayloadVersion $payloadVersion,
        public MediaItemLifecycleActorId $actor,
        public MediaItemLifecycleOccurredAt $occurredAt,
        public MediaItemLifecycleOccurredAt $recordedAt,
    ) {
        if ($recordedAt->value < $occurredAt->value) {
            throw new InvalidArgumentException('Media item lifecycle recorded time precedes occurrence.');
        }
    }

    /** @return array<string, int|string> */
    public function fields(): array
    {
        return [
            'eventType' => $this->eventType->value,
            'payloadVersion' => $this->payloadVersion->value,
            'actorId' => $this->actor->value,
            'occurredAt' => $this->occurredAt->canonical(),
            'recordedAt' => $this->recordedAt->canonical(),
        ];
    }
}
