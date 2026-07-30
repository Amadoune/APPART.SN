<?php

namespace Appart\Modules\Media\Application\MediaItemLifecycleEvent;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use InvalidArgumentException;

final readonly class MediaItemLifecycleEventPayload
{
    public function __construct(
        public MediaItemLifecycleEventId $eventId,
        public MediaItemLifecycleId $mediaId,
        public string $transition,
        public MediaItemLifecycleState $previousState,
        public MediaItemLifecycleState $currentState,
        public MediaItemLifecycleAction $action,
        public int $version,
        public int $occurredVersion,
    ) {
        if ($version !== 1
            || $occurredVersion < 2
            || $transition !== implode('>', [$previousState->value, $action->value, $currentState->value])) {
            throw new InvalidArgumentException('Invalid media item lifecycle event payload.');
        }
    }

    /** @return array<string, int|string> */
    public function fields(): array
    {
        return [
            'eventId' => $this->eventId->value,
            'aggregateType' => 'MediaItemLifecycle',
            'mediaId' => $this->mediaId->value,
            'transition' => $this->transition,
            'previousState' => $this->previousState->value,
            'currentState' => $this->currentState->value,
            'action' => $this->action->value,
            'version' => $this->version,
            'occurredVersion' => $this->occurredVersion,
        ];
    }
}
