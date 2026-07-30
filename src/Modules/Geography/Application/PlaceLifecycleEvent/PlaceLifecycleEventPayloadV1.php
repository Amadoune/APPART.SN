<?php

namespace Appart\Modules\Geography\Application\PlaceLifecycleEvent;

use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleAction;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleState;
use Appart\Modules\Geography\Application\PlaceLifecycle\PlaceLifecycleTransition;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use InvalidArgumentException;

final readonly class PlaceLifecycleEventPayloadV1
{
    public PlaceLifecycleEventPayloadVersion $version;

    public function __construct(
        public PlaceLifecycleEventId $eventId,
        public PlaceId $placeId,
        public PlaceLifecycleState $previousState,
        public PlaceLifecycleAction $action,
        public PlaceLifecycleState $currentState,
        public int $occurredVersion,
        public ?PlaceId $targetPlaceId,
    ) {
        $this->version = PlaceLifecycleEventPayloadVersion::V1;

        if ($occurredVersion < 2) {
            throw new InvalidArgumentException('A Place Lifecycle event version must be at least two.');
        }

        if (($action === PlaceLifecycleAction::Merge) !== ($targetPlaceId !== null)) {
            throw new InvalidArgumentException('Only a merge event may expose a target place identity.');
        }
    }

    public function transition(): PlaceLifecycleTransition
    {
        return new PlaceLifecycleTransition($this->previousState, $this->action, $this->currentState);
    }

    /** @return array<string, int|string> */
    public function fields(): array
    {
        $fields = [
            'placeId' => $this->placeId->value,
            'previousState' => $this->previousState->value,
            'action' => $this->action->value,
            'currentState' => $this->currentState->value,
            'occurredVersion' => $this->occurredVersion,
        ];

        if ($this->targetPlaceId !== null) {
            $fields['targetPlaceId'] = $this->targetPlaceId->value;
        }

        return $fields;
    }
}
